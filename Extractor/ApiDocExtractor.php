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
    const ANNOTATION_CLASS      = 'Nelmio\\ApiDocBundle\\Annotation\\ApiDoc';

    const FOS_REST_PARAM_CLASS  = 'FOS\\RestBundle\\Controller\\Annotations\\Param';

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
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

                    $array[] = $this->parseAnnotations($annot, $method, $route);
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
     * @return \ReflectionMethod|null
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
     * @param Route  $route
     * @return array|null
     */
    public function get($controller, $route)
    {
        if ($method = $this->getReflectionMethod($controller)) {
            if ($annot = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                if ($route = $this->router->getRouteCollection()->get($route)) {
                    return $this->parseAnnotations($annot, $method, $route);
                }
            }
        }

        return null;
    }

    protected function parseAnnotations($annotation, $method, $route)
    {
        $data = $this->getData($annotation, $route, $method);

        foreach ($this->reader->getMethodAnnotations($method) as $annot) {
            if (is_subclass_of($annot, self::FOS_REST_PARAM_CLASS)) {
                if ($annot->strict) {
                    $data['requirements'][$annot->name] = array(
                        'requirement'   => $annot->requirements,
                        'type'          => '',
                        'description'   => $annot->description,
                    );
                } else {
                    $data['annotation']->addFilter($annot->name, array(
                        'requirement'   => $annot->requirements,
                        'description'   => $annot->description,
                    ));
                }
            }
        }

        return $data;
    }

    /**
     * Allows to add more data to the ApiDoc object, and
     * returns an array containing the following keys:
     *  - annotation
     *  - route
     *
     * @param  ApiDoc            $annotation
     * @param  Route             $route
     * @param  \ReflectionMethod $method
     * @return array
     */
    protected function getData(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        $docblock = $this->getDocComment($method);

        if (null === $annotation->getDescription()) {
            $comments = explode("\n", $this->getDocCommentText($method));
            // just set the first line
            $comment = trim($comments[0]);
            $comment = preg_replace("#\n+#", ' ', $comment);
            $comment = preg_replace('#\s+#', ' ', $comment);
            $comment = preg_replace('#[_`*]+#', '', $comment);

            if ('@' !== substr($comment, 0, 1)) {
                $annotation->setDescription($comment);
            }
        }

        $annotation->setDocumentation($this->getDocCommentText($method));

        $paramDocs = array();
        foreach (explode("\n", $docblock) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
        }

        $route->setOptions(array_merge($route->getOptions(), array('_paramDocs' => $paramDocs)));

        return array('annotation' => $annotation, 'route' => $route, 'requirements' => array());
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

    protected function getDocCommentText(\Reflector $reflected)
    {
        $comment = $reflected->getDocComment();

        // Remove PHPDoc
        $comment = preg_replace('/^\s+\* @[\w0-9]+.*/msi', '', $comment);

        // let's clean the doc block
        $comment = str_replace('/**', '', $comment);
        $comment = str_replace('*/', '', $comment);
        $comment = preg_replace('/^\s*\* ?/m', '', $comment);

        return trim($comment);
    }
}
