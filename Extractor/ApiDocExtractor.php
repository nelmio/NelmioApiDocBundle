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
        $resources = array();
        foreach ($this->router->getRouteCollection()->all() as $route) {
            preg_match('#(.+)::([\w]+)#', $route->getDefault('_controller'), $matches);
            $method = new \ReflectionMethod($matches[1], $matches[2]);

            if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                if ($annot->isResource()) {
                    $resources[] = $route->getPattern();
                }

                $array[] = array('annotation' => $annot, 'route' => $route);
            }
        }

        rsort($resources);
        foreach ($array as $index => $element) {
            $hasResource = false;
            $pattern = $element['route']->getPattern();

            foreach ($resources as $resource) {
                if (0 === strpos($pattern, $resource)) {
                    $array[$index]['resource'] = $resource;

                    $hasResource = true;
                    break;
                }
            }

            if (false === $hasResource) {
                $array[$index]['resource'] = 'others';
            }
        }

        usort($array, function($a, $b) {
            if ($a['resource'] === $b['resource']) {
                if ($a['route']->getPattern() === $b['route']->getPattern()) {
                    return strcmp($a['route']->getRequirement('_method'), $b['route']->getRequirement('_method'));
                }

                return strcmp($a['route']->getPattern(), $b['route']->getPattern());
            }

            return strcmp($a['resource'], $b['resource']);
        });

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
