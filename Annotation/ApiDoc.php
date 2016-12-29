<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
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
     * Requirements are mandatory parameters in a route.
     *
     * @var array
     */
    private $requirements = array();

    /**
     * Filters are optional parameters in the query string.
     *
     * @var array
     */
    private $filters = array();

    /**
     * Parameters are data a client can send.
     *
     * @var array
     */
    private $parameters = array();
    /**
     * Headers that client can send.
     *
     * @var array
     */
    private $headers = array();

    /**
     * @var string
     */
    private $input = null;

    /**
     * @var string
     */
    private $output = null;

    /**
     * @var string
     */
    private $link = null;

    /**
     * Most of the time, a single line of text describing the action.
     *
     * @var string
     */
    private $description = null;

    /**
     * @var array
     */
    private $response = array();

    /**
     * @var bool
     */
    private $authentication = false;

    /**
     * @var array
     */
    private $authenticationRoles = array();

    /**
     * @var bool
     */
    private $deprecated = false;

    /**
     * @var array
     */
    private $statusCodes = array();

    /**
     * @var array
     */
    private $responseMap = array();

    /**
     * @var array
     */
    private $parsedResponseMap = array();

    /**
     * @var array
     */
    private $tags = array();

    public function __construct(array $data)
    {
        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        if (isset($data['input'])) {
            $this->input = $data['input'];
        }

        if (isset($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (!isset($filter['name'])) {
                    throw new \InvalidArgumentException('A "filter" element has to contain a "name" attribute');
                }

                $name = $filter['name'];
                unset($filter['name']);

                $this->addFilter($name, $filter);
            }
        }

        if (isset($data['requirements'])) {
            foreach ($data['requirements'] as $requirement) {
                if (!isset($requirement['name'])) {
                    throw new \InvalidArgumentException('A "requirement" element has to contain a "name" attribute');
                }

                $name = $requirement['name'];
                unset($requirement['name']);

                $this->addRequirement($name, $requirement);
            }
        }

        if (isset($data['parameters'])) {
            foreach ($data['parameters'] as $parameter) {
                if (!isset($parameter['name'])) {
                    throw new \InvalidArgumentException('A "parameter" element has to contain a "name" attribute');
                }

                if (!isset($parameter['dataType'])) {
                    throw new \InvalidArgumentException(sprintf(
                        '"%s" parameter element has to contain a "dataType" attribute',
                        $parameter['name']
                    ));
                }

                $name = $parameter['name'];
                unset($parameter['name']);

                $this->addParameter($name, $parameter);
            }
        }

        if (isset($data['headers'])) {
            foreach ($data['headers'] as $header) {
                if (!isset($header['name'])) {
                    throw new \InvalidArgumentException('A "header" element has to contain a "name" attribute');
                }

                $name = $header['name'];
                unset($header['name']);

                $this->addHeader($name, $header);
            }
        }

        if (isset($data['output'])) {
            $this->output = $data['output'];
        }

        if (isset($data['statusCodes'])) {
            foreach ($data['statusCodes'] as $statusCode => $description) {
                $this->addStatusCode($statusCode, $description);
            }
        }

        if (isset($data['authentication'])) {
            $this->setAuthentication((bool) $data['authentication']);
        }

        if (isset($data['authenticationRoles'])) {
            foreach ($data['authenticationRoles'] as $key => $role) {
                $this->authenticationRoles[] = $role;
            }
        }

        if (isset($data['deprecated'])) {
            $this->deprecated = $data['deprecated'];
        }

        if (isset($data['tags'])) {
            if (is_array($data['tags'])) {
                foreach ($data['tags'] as $tag => $colorCode) {
                    if (is_numeric($tag)) {
                        $this->addTag($colorCode);
                    } else {
                        $this->addTag($tag, $colorCode);
                    }
                }
            } else {
                $this->tags[] = $data['tags'];
            }
        }

        if (isset($data['responseMap'])) {
            $this->responseMap = $data['responseMap'];
            if (isset($this->responseMap[200])) {
                $this->output = $this->responseMap[200];
            }
        }
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
     * @param string $statusCode
     * @param mixed  $description
     */
    public function addStatusCode($statusCode, $description)
    {
        $this->statusCodes[$statusCode] = !is_array($description) ? array($description) : $description;
    }

    /**
     * @param string $tag
     * @param string $colorCode
     */
    public function addTag($tag, $colorCode = '#d9534f')
    {
        $this->tags[$tag] = $colorCode;
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
    public function getOutput()
    {
        return $this->output;
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
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @param string $name
     * @param array  $parameter
     */
    public function addParameter($name, array $parameter)
    {
        $this->parameters[$name] = $parameter;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param $name
     * @param array $header
     */
    public function addHeader($name, array $header)
    {
        $this->headers[$name] = $header;
    }

    /**
     * Sets the response data as processed by the parsers - same format as parameters.
     *
     * @param array $response
     */
    public function setResponse(array $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function getAuthentication()
    {
        return $this->authentication;
    }

    /**
     * @param bool $authentication
     */
    public function setAuthentication($authentication)
    {
        $this->authentication = $authentication;
    }

    /**
     * @return array
     */
    public function getAuthenticationRoles()
    {
        return $this->authenticationRoles;
    }

    /**
     * @param array $authenticationRoles
     */
    public function setAuthenticationRoles($authenticationRoles)
    {
        $this->authenticationRoles = $authenticationRoles;
    }

    /**
     * @return bool
     */
    public function getDeprecated()
    {
        return $this->deprecated;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @return array
     */
    public function getRequirements()
    {
        return $this->requirements;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param bool $deprecated
     *
     * @return $this
     */
    public function setDeprecated($deprecated)
    {
        $this->deprecated = (bool) $deprecated;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if ($description = $this->description) {
            $data['description'] = $description;
        }

        if ($link = $this->link) {
            $data['link'] = $link;
        }

        if ($filters = $this->filters) {
            $data['filters'] = $filters;
        }

        if ($parameters = $this->parameters) {
            $data['parameters'] = $parameters;
        }

        if ($headers = $this->headers) {
            $data['headers'] = $headers;
        }

        if ($requirements = $this->requirements) {
            $data['requirements'] = $requirements;
        }

        if ($response = $this->response) {
            $data['response'] = $response;
        }

        if ($parsedResponseMap = $this->parsedResponseMap) {
            $data['parsedResponseMap'] = $parsedResponseMap;
        }

        if ($statusCodes = $this->statusCodes) {
            $data['statusCodes'] = $statusCodes;
        }

        if ($tags = $this->tags) {
            $data['tags'] = $tags;
        }

        $data['authentication'] = $this->authentication;
        $data['authenticationRoles'] = $this->authenticationRoles;
        $data['deprecated'] = $this->deprecated;

        return $data;
    }

    /**
     * @return array
     */
    public function getResponseMap()
    {
        if (!isset($this->responseMap[200]) && null !== $this->output) {
            $this->responseMap[200] = $this->output;
        }

        return $this->responseMap;
    }

    /**
     * @return array
     */
    public function getParsedResponseMap()
    {
        return $this->parsedResponseMap;
    }

    /**
     * @param $model
     * @param $type
     * @param int $statusCode
     */
    public function setResponseForStatusCode($model, $type, $statusCode = 200)
    {
        $this->parsedResponseMap[$statusCode] = array('type' => $type, 'model' => $model);
        if ($statusCode == 200 && $this->response !== $model) {
            $this->response = $model;
        }
    }
}
