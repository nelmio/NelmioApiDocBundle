<?php

namespace Nelmio\ApiBundle\Extractor;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Routing\RouterInterface;

class ApiDocExtractor
{
    const ANNOTATION_CLASS = 'Nelmio\\ApiBundle\\Annotation\\ApiDoc';

    /**
     * @var
     */
    private $router;

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    public function __construct(RouterInterface $router, Reader $reader)
    {
        $this->router = $router;
        $this->reader = $reader;
    }

    public function all()
    {
        $array = array();
        foreach ($this->router->getRouteCollection()->all() as $route) {
            preg_match('#(.+)::([\w]+)#', $route->getDefault('_controller'), $matches);
            $method = new \ReflectionMethod($matches[1], $matches[2]);

            if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                $array[] = array('annotation' => $annot, 'route' => $route);
            }
        }

        return $array;
    }

    public function get($controller, $route)
    {
        preg_match('#(.+)::([\w]+)#', $controller, $matches);
        $method = new \ReflectionMethod($matches[1], $matches[2]);

        if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
            if ($route = $this->router->getRouteCollection()->get($route)) {
                return array('annotation' => $annot, 'route' => $route);
            }
        }

        return null;
    }
}
