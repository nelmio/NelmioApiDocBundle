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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiDocExtractor
{
    const ANNOTATION_CLASS = 'Nelmio\\ApiDocBundle\\Annotation\\ApiDoc';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \ymfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    public function __construct(ContainerInterface $container, RouterInterface $router, Reader $reader)
    {
        $this->container = $container;
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
            if ($method = $this->getReflectionMethod($route->getDefault('_controller'))) {
                if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                    if ($annot->isResource()) {
                        $resources[] = $route->getPattern();
                    }

                    $array[] = $this->getData($annot, $route, $method);
                }
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
     * Returns the ReflectionMethod for the given controller string
     *
     * @param string $controller
     * @return ReflectionMethod|null
     */
    public function getReflectionMethod($controller)
    {
        if (preg_match('#(.+)::([\w]+)#', $controller, $matches)) {
            $class = $matches[1];
            $method = $matches[2];
        } elseif (preg_match('#(.+):([\w]+)#', $controller, $matches)) {
            $controller = $matches[1];
            $method = $matches[2];
            if ($this->container->has($controller)) {
                $this->container->enterScope('request');
                $this->container->set('request', new Request);
                $class = get_class($this->container->get($controller));
                $this->container->leaveScope('request');
            }
        }

        if (isset($class) && isset($method)) {
            try {
                return new \ReflectionMethod($class, $method);
            } catch (\ReflectionException $e) {
            }
        }

        return null;
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
        if ($method = $this->getReflectionMethod($controller)) {
            if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                if ($route = $this->router->getRouteCollection()->get($route)) {
                    return $this->getData($annot, $route, $method);
                }
            }
        }

        return null;
    }

    /**
     * Allows to add more data to the ApiDoc object, and
     * returns an array containing the following keys:
     *  - annotation
     *  - route
     *
     * @param ApiDoc $annotation
     * @param Route $route
     * @param ReflectionMethod $method
     * @return array
     */
    protected function getData(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        $docblock = $this->getDocComment($method);

        if (null === $annotation->getDescription()) {
            $comments = explode("\n @", $docblock);
            // just set the first line
            $comment = trim($comments[0]);
            $comment = preg_replace("#[\n]+#", ' ', $comment);
            $comment = preg_replace('#[ ]+#', ' ', $comment);

            if ('@' !== substr($comment, 0, 1)) {
                $annotation->setDescription($comment);
            }
        }

        $paramDocs = array();
        foreach (explode("\n", $docblock) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
        }

        $route->addOptions(array('_paramDocs' => $paramDocs));

        return array('annotation' => $annotation, 'route' => $route);
    }

    protected function getDocComment(\Reflector $reflected)
    {
        $comment = $reflected->getDocComment();

        // let's clean the doc block
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = str_replace("\r", '', trim($comment));
        $comment = preg_replace("#^\n[ \t]+[*]?#i", "\n", trim($comment));
        $comment = preg_replace("#[\t ]+#i", ' ', trim($comment));
        $comment = str_replace("\"", "\\\"", $comment);

        return $comment;
    }
}
