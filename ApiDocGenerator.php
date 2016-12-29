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

use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Psr\Cache\CacheItemPoolInterface;

final class ApiDocGenerator
{
    private $swagger;
    private $describers;
    private $cacheItemPool;

    /**
     * @param DescriberInterface[] $describers
     */
    public function __construct(array $describers, CacheItemPoolInterface $cacheItemPool = null)
    {
        $this->describers = $describers;
        $this->cacheItemPool = $cacheItemPool;
    }

    public function generate(): Swagger
    {
        if (null !== $this->swagger) {
            return $this->swagger;
        }

        if ($this->cacheItemPool) {
            $item = $this->cacheItemPool->getItem('swagger_doc');
            if ($item->isHit()) {
                return $this->swagger = $item->get();
            }
        }

        $this->swagger = new Swagger();
        foreach ($this->describers as $describer) {
            $describer->describe($this->swagger);
        }

        if (isset($item)) {
            $this->cacheItemPool->save($item->set($this->swagger));
        }

        return $this->swagger;
    }
}
