<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle;

use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\ModelRegister;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerAwareTrait;

final class ApiDocGenerator
{
    use LoggerAwareTrait;

    /** @var OpenApi */
    private $openApi;

    /** @var iterable|DescriberInterface[] */
    private $describers;

    /** @var iterable|ModelDescriberInterface[] */
    private $modelDescribers;

    private ?CacheItemPoolInterface $cacheItemPool;

    private string $cacheItemId;

    /** @var string[] */
    private $alternativeNames = [];

    /** @var string[] */
    private $mediaTypes = ['json'];
    /**
     * @var ?string
     */
    private $openApiVersion;

    private Generator $generator;

    /**
     * @param DescriberInterface[]|iterable      $describers
     * @param ModelDescriberInterface[]|iterable $modelDescribers
     */
    public function __construct($describers, $modelDescribers, ?CacheItemPoolInterface $cacheItemPool = null, ?string $cacheItemId = null, ?Generator $generator = null)
    {
        $this->describers = $describers;
        $this->modelDescribers = $modelDescribers;
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheItemId = $cacheItemId ?? 'openapi_doc';
        $this->generator = $generator ?? new Generator($this->logger);
    }

    /**
     * @param string[] $alternativeNames
     */
    public function setAlternativeNames(array $alternativeNames): void
    {
        $this->alternativeNames = $alternativeNames;
    }

    /**
     * @param string[] $mediaTypes
     */
    public function setMediaTypes(array $mediaTypes): void
    {
        $this->mediaTypes = $mediaTypes;
    }

    public function setOpenApiVersion(?string $openApiVersion): void
    {
        $this->openApiVersion = $openApiVersion;
    }

    public function generate(): OpenApi
    {
        if (null !== $this->openApi) {
            return $this->openApi;
        }

        if (null !== $this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem($this->cacheItemId);
            if ($item->isHit()) {
                return $this->openApi = $item->get();
            }
        }

        if (null !== $this->openApiVersion) {
            $this->generator->setVersion($this->openApiVersion);
        }

        // Remove OperationId processor as we use a lot of generated annotations which do not have enough information in their context
        // to generate these ids properly.
        // @see \Nelmio\ApiDocBundle\OpenApiPhp\Util::createContext
        $this->generator->getProcessorPipeline()->remove(\OpenApi\Processors\OperationId::class);

        $context = Util::createContext(['version' => $this->generator->getVersion()]);

        $this->openApi = new OpenApi(['_context' => $context]);
        $modelRegistry = new ModelRegistry($this->modelDescribers, $this->openApi, $this->alternativeNames);
        if (null !== $this->logger) {
            $modelRegistry->setLogger($this->logger);
        }
        foreach ($this->describers as $describer) {
            if ($describer instanceof ModelRegistryAwareInterface) {
                $describer->setModelRegistry($modelRegistry);
            }

            $describer->describe($this->openApi);
        }

        $analysis = new Analysis([], $context);
        $analysis->addAnnotation($this->openApi, $context);

        // Register model attributes
        $modelRegister = new ModelRegister($modelRegistry, $this->mediaTypes);
        $modelRegister($analysis);

        // Calculate the associated schemas
        $modelRegistry->registerSchemas();

        $this->generator->getProcessorPipeline()->process($analysis);
        $analysis->validate();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openApi));
        }

        return $this->openApi;
    }
}
