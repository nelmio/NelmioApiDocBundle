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
use OpenApi\Annotations\OpenApi;
use Psr\Cache\CacheItemPoolInterface;

final class ApiDocGenerator
{
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

    public function setAlternativeNames(array $alternativeNames): void
    {
        $this->alternativeNames = $alternativeNames;
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

        $this->openApi = new OpenApi([]);
        $modelRegistry = new ModelRegistry($this->modelDescribers, $this->openApi, $this->alternativeNames);
        foreach ($this->describers as $describer) {
            if ($describer instanceof ModelRegistryAwareInterface) {
                $describer->setModelRegistry($modelRegistry);
            }

            $describer->describe($this->openApi);
        }
        $modelRegistry->registerSchemas();

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->openApi));
        }

        return $this->openApi;
    }
}
