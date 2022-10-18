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

    /** @var CacheItemPoolInterface|null */
    private $cacheItemPool;

    /** @var string|null */
    private $cacheItemId;

    /** @var string[] */
    private $alternativeNames = [];

    /** @var string[] */
    private $mediaTypes = ['json'];

    /**
     * @param DescriberInterface[]|iterable      $describers
     * @param ModelDescriberInterface[]|iterable $modelDescribers
     */
    public function __construct($describers, $modelDescribers, CacheItemPoolInterface $cacheItemPool = null, string $cacheItemId = null)
    {
        $this->describers = $describers;
        $this->modelDescribers = $modelDescribers;
        $this->cacheItemPool = $cacheItemPool;
        $this->cacheItemId = $cacheItemId;
    }

    public function setAlternativeNames(array $alternativeNames)
    {
        $this->alternativeNames = $alternativeNames;
    }

    public function setMediaTypes(array $mediaTypes)
    {
        $this->mediaTypes = $mediaTypes;
    }

    public function generate(): OpenApi
    {
        if (null !== $this->openApi) {
            return $this->openApi;
        }

        if ($this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem($this->cacheItemId ?? 'openapi_doc');
            if ($item->isHit()) {
                return $this->openApi = $item->get();
            }
        }

        $generator = new Generator();
        // Remove OperationId processor as we use a lot of generated annotations which do not have enough information in their context
        // to generate these ids properly.
        // @see https://github.com/zircote/swagger-php/issues/1153
        $generator->setProcessors(array_filter($generator->getProcessors(), function ($processor) {
            return !$processor instanceof \OpenApi\Processors\OperationId;
        }));

        $context = Util::createContext(['version' => $generator->getVersion()]);

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

        // Register model annotations
        $modelRegister = new ModelRegister($modelRegistry, $this->mediaTypes);
        $modelRegister($analysis);

        // Calculate the associated schemas
        $modelRegistry->registerSchemas();

        $analysis->process($generator->getProcessors());
        $analysis->validate();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openApi));
        }

        return $this->openApi;
    }
}
