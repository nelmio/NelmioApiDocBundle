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
use Nelmio\ApiDocBundle\OpenApiPhp\DefaultOperationId;
use Nelmio\ApiDocBundle\OpenApiPhp\ModelRegister;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Analysis;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;

final class ApiDocGenerator
{
    use LoggerAwareTrait;

    private ?OpenApi $openApi = null;

    /** @var string[] */
    private array $alternativeNames = [];

    /** @var string[] */
    private array $mediaTypes = ['json'];

    /**
     * @param iterable|DescriberInterface[]      $describers
     * @param ModelDescriberInterface[]|iterable $modelDescribers
     */
    public function __construct(
        private iterable $describers,
        private iterable $modelDescribers,
        private ?CacheItemPoolInterface $cacheItemPool = null,
        private ?string $cacheItemId = null
    ) {
    }

    public function setAlternativeNames(array $alternativeNames)
    {
        $this->alternativeNames = $alternativeNames;
    }

    public function setMediaTypes(array $mediaTypes)
    {
        $this->mediaTypes = $mediaTypes;
    }

    /**
     * @throws InvalidArgumentException
     */
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

        $this->openApi = new OpenApi([]);
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

        $context = Util::createContext();
        $analysis = new Analysis([], $context);
        $analysis->addAnnotation($this->openApi, $context);

        // Register model annotations
        $modelRegister = new ModelRegister($modelRegistry, $this->mediaTypes);
        $modelRegister($analysis);

        // Calculate the associated schemas
        $modelRegistry->registerSchemas();

        $defaultOperationIdProcessor = new DefaultOperationId();
        $defaultOperationIdProcessor($analysis);

        $analysis->process((new Generator())->getProcessors());
        $analysis->validate();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openApi));
        }

        return $this->openApi;
    }
}
