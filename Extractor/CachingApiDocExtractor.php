<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CachingApiDocExtractor
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class CachingApiDocExtractor extends ApiDocExtractor
{
    /**
     * @var \Symfony\Component\Config\ConfigCache
     */
    protected $cache;

    protected $cacheFile;

    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        Reader $reader,
        DocCommentExtractor $commentExtractor,
        array $handlers,
        $cacheFile,
        $debug = false
    ) {
        parent::__construct($container, $router, $reader, $commentExtractor, $handlers);
        $this->cacheFile = $cacheFile;
        $this->cache = new ConfigCache($this->cacheFile, $debug);
    }

    public function all()
    {
        if ($this->cache->isFresh() === false) {

            $resources = array();

            foreach ($this->getRoutes() as $route) {
                if ( null !== ($method = $this->getReflectionMethod($route->getDefault('_controller')))
                  && null !== ($annotation = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS))) {
                    $file = $method->getDeclaringClass()->getFileName();
                    $resources[] = new FileResource($file);
                }
            }

            $resources = array_merge($resources, $this->router->getRouteCollection()->getResources());

            $data = parent::all();
            $this->cache->write(serialize($data), $resources);

            return $data;
        }

        return unserialize(file_get_contents($this->cacheFile));

    }
}
