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
use Symfony\Component\Routing\RouterInterface;

class ApiDocExtractor
{
    const ANNOTATION_CLASS = 'Nelmio\\ApiDocBundle\\Annotation\\ApiDoc';

    /**
     * @var \ymfony\Component\Routing\RouterInterface
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

    /**
     * Returns an array of data where each data is an array with the following keys:
     *  - annotation
     *  - route
     *  - resource
     *
     * @return array
     */
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

        $methodOrder = array('GET', 'POST', 'PUT', 'DELETE');

        usort($array, function($a, $b) use ($methodOrder) {
            if ($a['resource'] === $b['resource']) {
                if ($a['route']->getPattern() === $b['route']->getPattern()) {
                    $methodA = array_search($a['route']->getRequirement('_method'), $methodOrder);
                    $methodB = array_search($b['route']->getRequirement('_method'), $methodOrder);

                    if ($methodA === $methodB) {
                        return strcmp($a['route']->getRequirement('_method'), $b['route']->getRequirement('_method'));
                    }

                    return $methodA > $methodB ? 1 : -1;
                }

                return strcmp($a['route']->getPattern(), $b['route']->getPattern());
            }

            return strcmp($a['resource'], $b['resource']);
        });

        return $array;
    }

    /**
     * Returns an array containing two values with the following keys:
     *  - annotation
     *  - route
     *
     * @param string $controller
     * @param Route $route
     * @return array|null
     */
    public function get($controller, $route)
    {
        if (!preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            return null;
        }

        try {
            $method = new \ReflectionMethod($matches[1], $matches[2]);
        } catch (\ReflectionException $e) {
            return null;
        }

        if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
            if ($route = $this->router->getRouteCollection()->get($route)) {
                return array('annotation' => $annot, 'route' => $route);
            }
        }

        return null;
    }
}
