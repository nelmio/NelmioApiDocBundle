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
use Nelmio\ApiDocBundle\Parser\ParserInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;

class ApiDocExtractor
{
    const ANNOTATION_CLASS                = 'Nelmio\\ApiDocBundle\\Annotation\\ApiDoc';

    const FOS_REST_QUERY_PARAM_CLASS      = 'FOS\\RestBundle\\Controller\\Annotations\\QueryParam';

    const FOS_REST_REQUEST_PARAM_CLASS    = 'FOS\\RestBundle\\Controller\\Annotations\\RequestParam';

    const JMS_SECURITY_EXTRA_SECURE_CLASS = 'JMS\\SecurityExtraBundle\\Annotation\\Secure';

    const CACHE_ANNOTATION_CLASS          = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache';

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var DocCommentExtractor
     */
    private $commentExtractor;

    /**
     * @var array ParserInterface
     */
    protected $parsers = array();

    public function __construct(ContainerInterface $container, RouterInterface $router, Reader $reader, DocCommentExtractor $commentExtractor)
    {
        $this->container = $container;
        $this->router    = $router;
        $this->reader    = $reader;
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * Return a list of route to inspect for ApiDoc annotation
     * You can extend this method if you don't want all the routes
     * to be included.
     *
     * @return array of Route
     */
    public function getRoutes()
    {
        return $this->router->getRouteCollection()->all();
    }

    /**
     * Returns an array of data where each data is an array with the following keys:
     *  - annotation
     *  - resource
     *
     * @return array
     */
    public function all()
    {
        $array = array();
        $resources = array();

        foreach ($this->getRoutes() as $route) {
            if ($method = $this->getReflectionMethod($route->getDefault('_controller'))) {
                if ($annotation = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                    if ($annotation->isResource()) {
                        // remove format from routes used for resource grouping
                        $resources[] = str_replace('.{_format}', '', $route->getPattern());
                    }

                    $array[] = array('annotation' => $this->extractData($annotation, $route, $method));
                }
            }
        }

        rsort($resources);
        foreach ($array as $index => $element) {
            $hasResource = false;
            $pattern     = $element['annotation']->getRoute()->getPattern();

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
                if ($a['annotation']->getRoute()->getPattern() === $b['annotation']->getRoute()->getPattern()) {
                    $methodA = array_search($a['annotation']->getRoute()->getRequirement('_method'), $methodOrder);
                    $methodB = array_search($b['annotation']->getRoute()->getRequirement('_method'), $methodOrder);

                    if ($methodA === $methodB) {
                        return strcmp(
                            $a['annotation']->getRoute()->getRequirement('_method'),
                            $b['annotation']->getRoute()->getRequirement('_method')
                        );
                    }

                    return $methodA > $methodB ? 1 : -1;
                }

                return strcmp(
                    $a['annotation']->getRoute()->getPattern(),
                    $b['annotation']->getRoute()->getPattern()
                );
            }

            return strcmp($a['resource'], $b['resource']);
        });

        return $array;
    }

    /**
     * Returns the ReflectionMethod for the given controller string.
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
                $this->container->set('request', new Request());
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
     * Returns an ApiDoc annotation.
     *
     * @param string $controller
     * @param Route  $route
     * @return ApiDoc|null
     */
    public function get($controller, $route)
    {
        if ($method = $this->getReflectionMethod($controller)) {
            if ($annotation = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS)) {
                if ($route = $this->router->getRouteCollection()->get($route)) {
                    return $this->extractData($annotation, $route, $method);
                }
            }
        }

        return null;
    }

    /**
     * Registers a class parser to use for parsing input class metadata
     *
     * @param ParserInterface $parser
     */
    public function addParser(ParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    /**
     * Returns a new ApiDoc instance with more data.
     *
     * @param  ApiDoc            $annotation
     * @param  Route             $route
     * @param  \ReflectionMethod $method
     * @return ApiDoc
     */
    protected function extractData(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        // create a new annotation
        $annotation = clone $annotation;

        // parse annotations
        $this->parseAnnotations($annotation, $route, $method);

        // route
        $annotation->setRoute($route);

        // description
        if (null === $annotation->getDescription()) {
            $comments = explode("\n", $this->commentExtractor->getDocCommentText($method));
            // just set the first line
            $comment = trim($comments[0]);
            $comment = preg_replace("#\n+#", ' ', $comment);
            $comment = preg_replace('#\s+#', ' ', $comment);
            $comment = preg_replace('#[_`*]+#', '', $comment);

            if ('@' !== substr($comment, 0, 1)) {
                $annotation->setDescription($comment);
            }
        }

        // doc
        $annotation->setDocumentation($this->commentExtractor->getDocCommentText($method));

        // input (populates 'parameters' for the formatters)
        if (null !== $input = $annotation->getInput()) {
            $parameters = array();

            foreach ($this->parsers as $parser) {
                if ($parser->supports($input)) {
                    $parameters = $parser->parse($input);
                    break;
                }
            }

            if ('PUT' === $method) {
                // All parameters are optional with PUT (update)
                array_walk($parameters, function($val, $key) use (&$data) {
                    $parameters[$key]['required'] = false;
                });
            }

            $annotation->setParameters($parameters);
        }

        // output (populates 'response' for the formatters)
        if (null !== $output = $annotation->getOutput()) {
            $response = array();

            foreach ($this->parsers as $parser) {
                if ($parser->supports($output)) {
                    $response = $parser->parse($output);
                    break;
                }
            }

            $annotation->setResponse($response);
        }

        // requirements
        $requirements = array();
        foreach ($route->getRequirements() as $name => $value) {
            if ('_method' !== $name) {
                $requirements[$name] = array(
                    'requirement'   => $value,
                    'dataType'      => '',
                    'description'   => '',
                );
            }
            if ('_scheme' == $name) {
                $https = ('https' == $value);
                $annotation->setHttps($https);
            }
        }

        $paramDocs = array();
        foreach (explode("\n", $this->commentExtractor->getDocComment($method)) as $line) {
            if (preg_match('{^@param (.+)}', trim($line), $matches)) {
                $paramDocs[] = $matches[1];
            }
        }

        $regexp = '{(\w*) *\$%s *(.*)}i';
        foreach ($route->compile()->getVariables() as $var) {
            $found = false;
            foreach ($paramDocs as $paramDoc) {
                if (preg_match(sprintf($regexp, preg_quote($var)), $paramDoc, $matches)) {
                    $requirements[$var]['dataType']    = isset($matches[1]) ? $matches[1] : '';
                    $requirements[$var]['description'] = $matches[2];

                    if (!isset($requirements[$var]['requirement'])) {
                        $requirements[$var]['requirement'] = '';
                    }

                    $found = true;
                    break;
                }
            }

            if (!isset($requirements[$var]) && false === $found) {
                $requirements[$var] = array('requirement' => '', 'dataType' => '', 'description' => '');
            }
        }

        $annotation->setRequirements($requirements);

        return $annotation;
    }

    /**
     * Parses annotations for a given method, and adds new information to the given ApiDoc
     * annotation. Useful to extract information from the FOSRestBundle annotations.
     *
     * @param ApiDoc           $annotation
     * @param Route            $route
     * @param ReflectionMethod $method
     */
    protected function parseAnnotations(ApiDoc $annotation, Route $route, \ReflectionMethod $method)
    {
        foreach ($this->reader->getMethodAnnotations($method) as $annot) {
            if (is_a($annot, self::FOS_REST_QUERY_PARAM_CLASS)) {
                if ($annot->strict && $annot->default === null) {
                    $annotation->addRequirement($annot->name, array(
                        'requirement'   => $annot->requirements,
                        'dataType'      => '',
                        'description'   => $annot->description,
                    ));
                } else {
                    $annotation->addFilter($annot->name, array(
                        'requirement'   => $annot->requirements,
                        'description'   => $annot->description,
                    ));
                }
            } elseif (is_a($annot, self::FOS_REST_REQUEST_PARAM_CLASS)) {
                $annotation->addParameter($annot->name, array(
                    'required'    => $annot->strict && $annot->default === null,
                    'dataType'    => $annot->requirements,
                    'description' => $annot->description,
                    'readonly'    => false
                ));
            } elseif (is_a($annot, self::JMS_SECURITY_EXTRA_SECURE_CLASS)) {
                $annotation->setAuthentication(true);
            }  elseif (is_a($annot, self::CACHE_ANNOTATION_CLASS)) {
                $annotation->setCache($annot->getMaxAge());
            }
        }
    }
}
