<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

use Symfony\Component\Routing\Route;

/**
 * @Annotation
 */
class ApiDoc
{
    /**
     * @var array
     */
    private $filters  = array();

    /**
     * @var string
     */
    private $input = null;

    /**
     * @var string
     */
    private $return = null;

    /**
     * @var string
     */
    private $description = null;

    /**
     * @var string
     */
    private $documentation = null;

    /**
     * @var Boolean
     */
    private $isResource = false;

    /**
     * @var array
     */
    private $requirements = array();

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @var array
     */
    private $response = array();

    /**
     * @var Route
     */
    private $route;

    /**
     * @var array
     */
    private $statusCodes = array();

    public function __construct(array $data)
    {
        if (isset($data['input'])) {
            $this->input = $data['input'];
        } elseif (isset($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (!isset($filter['name'])) {
                    throw new \InvalidArgumentException('A "filter" element has to contain a "name" attribute');
                }

                $name = $filter['name'];
                unset($filter['name']);

                $this->addFilter($name, $filter);
            }
        }

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        if (isset($data['return'])) {
            $this->return = $data['return'];
        }

        if (isset($data['statusCodes'])) {
            $this->statusCodes = $data['statusCodes'];
        }

        $this->isResource = isset($data['resource']) && $data['resource'];
    }

    /**
     * @param string $name
     * @param array  $filter
     */
    public function addFilter($name, array $filter)
    {
        $this->filters[$name] = $filter;
    }

    /**
     * @param string $name
     * @param array  $requirement
     */
    public function addRequirement($name, array $requirement)
    {
        $this->requirements[$name] = $requirement;
    }

    /**
     * @param array $requirements
     */
    public function setRequirements(array $requirements)
    {
        $this->requirements = array_merge($this->requirements, $requirements);
    }

    /**
     * @return string|null
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return string|null
     */
    public function getReturn()
    {
        return $this->return;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return Boolean
     */
    public function isResource()
    {
        return $this->isResource;
    }

    /**
     * @var string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @var string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Sets the responsed data as processed by the parsers - same format as parameters
     *
     * @param array $response
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Route $route
     */
    public function setRoute(Route $route)
    {
        $this->route = $route;
    }

    /**
     * @return Route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array
     */
    public function getStatusCodes()
    {
        return $this->statusCodes;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array(
            'method'        => $this->method,
            'uri'           => $this->uri,
        );

        if ($description = $this->description) {
            $data['description'] = $description;
        }

        if ($documentation = $this->documentation) {
            $data['documentation'] = $documentation;
        }

        if ($filters = $this->filters) {
            $data['filters'] = $filters;
        }

        if ($parameters = $this->parameters) {
            $data['parameters'] = $parameters;
        }

        if ($requirements = $this->requirements) {
            $data['requirements'] = $requirements;
        }

        if ($response = $this->response) {
            $data['response'] = $response;
        }

        if ($statusCodes = $this->statusCodes) {
            $data['statusCodes'] = $statusCodes;
        }

        return $data;
    }
}
