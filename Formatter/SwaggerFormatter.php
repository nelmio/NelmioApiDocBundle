<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;


use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\DataTypes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;

/**
 * Produces Swagger-compliant resource lists and API declarations as defined here:
 * https://github.com/wordnik/swagger-spec/blob/master/versions/1.2.md
 *
 * This formatter produces an array. Therefore output still needs to be `json_encode`d before passing on as HTTP response.
 *
 * @author Bezalel Hermoso <bezalelhermoso@gmail.com>
 */
class SwaggerFormatter implements FormatterInterface
{
    protected $basePath;

    protected $apiVersion;

    protected $swaggerVersion;

    protected $info = array();

    protected $typeMap = array(
        DataTypes::INTEGER => 'integer',
        DataTypes::FLOAT => 'number',
        DataTypes::STRING => 'string',
        DataTypes::BOOLEAN => 'boolean',
        DataTypes::FILE => 'string',
        DataTypes::DATE => 'string',
        DataTypes::DATETIME => 'string',
    );

    protected $formatMap = array(
        DataTypes::INTEGER => 'int32',
        DataTypes::FLOAT => 'float',
        DataTypes::FILE => 'byte',
        DataTypes::DATE => 'date',
        DataTypes::DATETIME => 'date-time',
    );

    /**
     * Format a collection of documentation data.
     *
     * If resource is provided, an API declaration for that resource is produced. Otherwise, a resource listing is returned.
     *
     * @param array|ApiDoc[] $collection
     * @param null|string $resource
     * @return string|array
     */
    public function format(array $collection, $resource = null)
    {
        if ($resource === null) {
            return $this->produceResourceListing($collection);
        } else {
            return $this->produceApiDeclaration($collection, $resource);
        }
    }

    /**
     * Formats the collection into Swagger-compliant output.
     *
     * @param array $collection
     * @return array
     */
    public function produceResourceListing(array $collection)
    {
        $resourceList = array(
            'swaggerVersion' => (string) $this->swaggerVersion,
            'apis' => array(),
            'apiVersion' => (string) $this->apiVersion,
            'info' => $this->getInfo(),
            'authorizations' => $this->getAuthorizations(),
        );

        $apis = &$resourceList['apis'];

        foreach ($collection as $item) {

            /** @var $apiDoc ApiDoc */
            $apiDoc = $item['annotation'];
            $resource = $item['resource'];

            if (!$apiDoc->isResource()) {
                continue;
            }

            $subPath = $this->stripBasePath($resource);
            $normalizedName = $this->normalizeResourcePath($subPath);

            $apis[] = array(
                'path' => '/' . $normalizedName,
                'description' => $apiDoc->getResourceDescription(),
            );

        }

        return $resourceList;
    }

    protected function getAuthorizations()
    {
        return array();
    }

    /**
     * @return array
     */
    protected function getInfo()
    {
        return $this->info;
    }

    /**
     * Format documentation data for one route.
     *
     * @param ApiDoc $annotation
     * return string|array
     * @throws \BadMethodCallException
     */
    public function formatOne(ApiDoc $annotation)
    {
        throw new \BadMethodCallException(sprintf('%s does not support formatting a single ApiDoc only.', __CLASS__));
    }

    /**
     * Formats collection to produce a Swagger-compliant API declaration for the given resource.
     *
     * @param array $collection
     * @param string $resource
     * @return array
     */
    protected function produceApiDeclaration(array $collection, $resource)
    {

        $apiDeclaration = array(
            'swaggerVersion' => (string) $this->swaggerVersion,
            'apiVersion' => (string) $this->apiVersion,
            'basePath' => $this->basePath,
            'resourcePath' => $resource,
            'apis' => array(),
            'models' => array(),
            'produces' => array(),
            'consumes' => array(),
            'authorizations' => array(),
        );

        $main = null;

        $apiBag = array();

        $models = array();


        foreach ($collection as $item) {

            /** @var $apiDoc ApiDoc */
            $apiDoc = $item['annotation'];
            $itemResource = $this->stripBasePath($item['resource']);

            $route = $apiDoc->getRoute();

            $itemResource = $this->normalizeResourcePath($itemResource);

            if ('/' . $itemResource !== $resource) {
                continue;
            }

            $compiled = $route->compile();

            $path = $this->stripBasePath($route->getPath());

            if (!isset($apiBag[$path])) {
                $apiBag[$path] = array();
            }

            $parameters = array();
            $responseMessages = array();

            foreach ($compiled->getPathVariables() as $paramValue) {
                $parameter = array(
                    'paramType' => 'path',
                    'name' => $paramValue,
                    'type' => 'string',
                    'required' => true,
                );

                if ($paramValue === '_format' && false != ($req = $route->getRequirement('_format'))) {
                    $parameter['enum'] = explode('|', $req);
                }

                $parameters[] = $parameter;
            }

            if (isset($data['filters'])) {
                $parameters = array_merge($parameters, $this->deriveQueryParameters($data['filters']));
            }

            $data = $apiDoc->toArray();

            if (isset($data['parameters'])) {
                $parameters = array_merge($parameters, $this->deriveParameters($data['parameters'], $models));
            }

            $responseMap = $apiDoc->getParsedResponseMap();

            $statusMessages = isset($data['statusCodes']) ? $data['statusCodes'] : array();

            foreach ($responseMap as $statusCode => $prop) {

                if (isset($statusMessages[$statusCode])) {
                    $message = is_array($statusMessages[$statusCode]) ? implode('; ', $statusMessages[$statusCode]) : $statusCode[$statusCode];
                } else {
                    $message = sprintf('See standard HTTP status code reason for %s', $statusCode);
                }

                $className = !empty($prop['type']['form_errors']) ? $prop['type']['class'] . '.ErrorResponse' : $prop['type']['class'];

                $responseModel = array(
                    'code' => $statusCode,
                    'message' => $message,
                    'responseModel' => $this->registerModel($className, $prop['model'], '', $models),
                );
                $responseMessages[$statusCode] = $responseModel;
            }

            $unmappedMessages = array_diff(array_keys($statusMessages), array_keys($responseMessages));

            foreach ($unmappedMessages as $code) {
                $responseMessages[$code] = array(
                    'code' => $code,
                    'message' => is_array($statusMessages[$code]) ? implode('; ', $statusMessages[$code]) : $statusMessages[$code],
                );
            }

            $type = isset($responseMessages[200]['responseModel']) ? $responseMessages[200]['responseModel'] : null;

            foreach ($apiDoc->getRoute()->getMethods() as $method) {
                $operation = array(
                    'method' => $method,
                    'summary' => $apiDoc->getDescription(),
                    'nickname' => $this->generateNickname($method, $itemResource),
                    'parameters' => $parameters,
                    'responseMessages' => array_values($responseMessages),
                );

                if ($type !== null) {
                    $operation['type'] = $type;
                }

                $apiBag[$path][] = $operation;
            }
        }

        $apiDeclaration['resourcePath'] = $resource;

        foreach ($apiBag as $path => $operations) {
            $apiDeclaration['apis'][] = array(
                'path' => $path,
                'operations' => $operations,
            );
        }

        $apiDeclaration['models'] = $models;

        return $apiDeclaration;
    }

    /**
     * Slugify a URL path. Trims out path parameters wrapped in curly brackets.
     *
     * @param $path
     * @return string
     */
    protected function normalizeResourcePath($path)
    {
        $path = preg_replace('/({.*?})/', '', $path);
        $path = trim(preg_replace('/[^0-9a-zA-Z]/', '-', $path), '-');
        $path = preg_replace('/-+/', '-', $path);
        return $path;
    }

    /**
     * @param $path
     */
    public function setBasePath($path)
    {
        $this->basePath = $path;
    }

    /**
     * Formats query parameters to Swagger-compliant form.
     *
     * @param array $input
     * @return array
     */
    protected function deriveQueryParameters(array $input)
    {
        $parameters = array();

        foreach ($input as $name => $prop) {
            $parameters[] = array(
                'paramType' => 'query',
                'name' => $name,
                'type' => isset($this->typeMap[$prop['dataType']]) ? $this->typeMap[$prop['dataType']] : 'string',
            );
        }

        return $parameters;

    }

    /**
     * Builds a Swagger-compliant parameter list from the provided parameter array. Models are built when necessary.
     *
     * @param array $input
     * @param array $models
     * @return array
     */
    protected function deriveParameters(array $input, array &$models)
    {

        $parameters = array();

        foreach ($input as $name => $prop) {

            $type = null;
            $format = null;
            $ref = null;
            $enum = null;
            $items = null;

            if (isset ($this->typeMap[$prop['actualType']])) {
                $type = $this->typeMap[$prop['actualType']];
            } else {
                switch ($prop['actualType']) {
                    case DataTypes::ENUM:
                        $type = 'string';
                        if (isset($prop['format'])) {
                            $enum = array_keys(json_decode($prop['format'], true));
                        }
                        break;

                    case DataTypes::MODEL:
                        $ref =
                            $this->registerModel(
                                $prop['subType'],
                                isset($prop['children']) ? $prop['children'] : null,
                                $prop['description'] ?: $prop['dataType'],
                                $models
                            );
                        break;
                }
            }

            if (isset($this->formatMap[$prop['actualType']])) {
                $format = $this->formatMap[$prop['actualType']];
            }

            if (null === $type && null === $ref) {
                /* `type` or `$ref` is required. Continue to next of none of these was determined. */
                continue;
            }

            $parameter = array(
                'paramType' => 'form',
                'name' => $name,
            );

            if (null !== $type) {
                $parameter['type'] = $type;
            }

            if (null !== $ref) {
                $parameter['$ref'] = $ref;
                $parameter['type'] = $ref;
            }

            if (null !== $format) {
                $parameter['format'] = $format;
            }

            if (is_array($enum) && count($enum) > 0) {
                $parameter['enum'] = $enum;
            }

            if ($prop['default'] !== null) {
                $parameter['defaultValue'] = $prop['default'];
            }

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * Registers a model into the model array. Returns a unique identifier for the model to be used in `$ref` properties.
     *
     * @param $className
     * @param array $parameters
     * @param string $description
     * @param $models
     * @return mixed
     */
    public function registerModel($className, array $parameters = null, $description = '', &$models)
    {
        if (isset ($models[$className])) {
            return $models[$className]['id'];
        }

        /*
         * Converts \Fully\Qualified\Class\Name to Fully.Qualified.Class.Name
         */
        $id = preg_replace('#(\\\|[^A-Za-z0-9])#', '.', $className);
        //Replace duplicate dots.
        $id = preg_replace('/\.+/', '.', $id);
        //Replace trailing dots.
        $id = preg_replace('/^\./', '', $id);

        $model = array(
            'id' => $id,
            'description' => $description,
        );

        if (is_array($parameters)) {

            $required = array();
            $properties = array();

            foreach ($parameters as $name => $prop) {

                $subParam = array();

                if ($prop['actualType'] === DataTypes::MODEL) {

                    $subParam['$ref'] = $this->registerModel(
                        $prop['subType'],
                        isset($prop['children']) ? $prop['children'] : null,
                        $prop['description'] ?: $prop['dataType'],
                        $models
                    );

                } else {

                    $type = null;
                    $format = null;
                    $items = null;
                    $enum = null;
                    $ref = null;

                    if (isset($this->typeMap[$prop['actualType']])) {
                        $type = $this->typeMap[$prop['actualType']];
                    } else{

                        switch ($prop['actualType']) {
                            case DataTypes::ENUM:
                                $type = 'string';
                                if (isset($prop['format'])) {
                                    $enum = array_keys(json_decode($prop['format'], true));
                                }
                                break;

                            case DataTypes::COLLECTION:
                                $type = 'array';

                                if ($prop['subType'] === DataTypes::MODEL) {

                                } else {

                                    if ($prop['subType'] === null
                                    || isset($this->typeMap[$prop['subType']])) {
                                        $items = array(
                                            'type' => 'string',
                                        );
                                    } elseif (!isset($this->typeMap[$prop['subType']])) {
                                        $items = array(
                                            '$ref' =>
                                                $this->registerModel(
                                                    $prop['subType'],
                                                    isset($prop['children']) ? $prop['children'] : null,
                                                    $prop['description'] ?: $prop['dataType'],
                                                    $models
                                                )
                                        );
                                    }
                                }
                                /* @TODO: Handle recursion if subtype is a model. */
                                break;

                            case DataTypes::MODEL:
                                $ref = $this->registerModel(
                                    $prop['subType'],
                                    isset($prop['children']) ? $prop['children'] : null,
                                    $prop['description'] ?: $prop['dataType'],
                                    $models
                                );
                                $type = $ref;
                                /* @TODO: Handle recursion. */
                                break;
                        }
                    }

                    if (isset($this->formatMap[$prop['actualType']])) {
                        $format = $this->formatMap[$prop['actualType']];
                    }

                    $subParam = array(
                        'type' => $type,
                        'description' => empty($prop['description']) === false ? (string) $prop['description'] : $prop['dataType'],
                    );

                    if ($format !== null) {
                        $subParam['format'] = $format;
                    }

                    if ($enum !== null) {
                        $subParam['enum'] = $enum;
                    }

                    if ($ref !== null) {
                        $subParam['$ref'] = $ref;
                    }

                    if ($items !== null) {
                        $subParam['items'] = $items;
                    }

                    if ($prop['required']) {
                        $required[] = $name;
                    }

                }

                $properties[$name] = $subParam;
            }

            $model['properties'] = $properties;
            $model['required'] = $required;
            $models[$id] = $model;
        }

        return $id;
    }

    /**
     * @param mixed $swaggerVersion
     */
    public function setSwaggerVersion($swaggerVersion)
    {
        $this->swaggerVersion = $swaggerVersion;
    }

    /**
     * @param mixed $apiVersion
     */
    public function setApiVersion($apiVersion)
    {
        $this->apiVersion = $apiVersion;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * Strips the base path from a URL path.
     *
     * @param $path
     * @return mixed
     */
    protected function stripBasePath($path)
    {
        $pattern = sprintf('#%s#', preg_quote($this->basePath));
        $subPath = preg_replace($pattern, '', $path);
        return $subPath;
    }

    /**
     * Generate nicknames based on support HTTP methods and the resource name.
     *
     * @param $method
     * @param $resource
     * @return string
     */
    protected function generateNickname($method, $resource)
    {
        $resource = preg_replace('#/^#', '', $resource);
        $resource = $this->normalizeResourcePath($resource);
        return sprintf('%s_%s', strtolower($method), $resource);
    }
}