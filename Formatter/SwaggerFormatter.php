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
use Nelmio\ApiDocBundle\Swagger\ModelRegistry;
use Symfony\Component\HttpFoundation\Response;

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
     * @var \Nelmio\ApiDocBundle\Swagger\ModelRegistry
     */
    protected $modelRegistry;

    public function __construct($namingStategy)
    {
        $this->modelRegistry = new ModelRegistry($namingStategy);
    }

    /**
     * @var array
     */
    protected $authConfig = null;

    public function setAuthenticationConfig(array $config)
    {
        $this->authConfig = $config;
    }

    /**
     * Format a collection of documentation data.
     *
     * If resource is provided, an API declaration for that resource is produced. Otherwise, a resource listing is returned.
     *
     * @param  array|ApiDoc[] $collection
     * @param  null|string    $resource
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
     * @param  array $collection
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
        $auth = array();

        if ($this->authConfig === null) {
            return $auth;
        }

        $config = $this->authConfig;

        if ($config['delivery'] === 'http') {
            return $auth;
        }

        $auth['apiKey'] = array(
            'type' => 'apiKey',
            'passAs' => $config['delivery'],
            'keyname' => $config['name'],
        );

        return $auth;
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
     * @param  ApiDoc                  $annotation
     *                                             return string|array
     * @throws \BadMethodCallException
     */
    public function formatOne(ApiDoc $annotation)
    {
        throw new \BadMethodCallException(sprintf('%s does not support formatting a single ApiDoc only.', __CLASS__));
    }

    /**
     * Formats collection to produce a Swagger-compliant API declaration for the given resource.
     *
     * @param  array  $collection
     * @param  string $resource
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
            'authorizations' => $this->getAuthorizations(),
        );

        $main = null;

        $apiBag = array();

        foreach ($collection as $item) {

            /** @var $apiDoc ApiDoc */
            $apiDoc = $item['annotation'];
            $itemResource = $this->stripBasePath($item['resource']);
            $input = $apiDoc->getInput();

            if (!is_array($input)) {
                $input = array(
                    'class' => $input,
                    'paramType' => 'form',
                );
            } elseif (empty($input['paramType'])) {
                $input['paramType'] = 'form';
            }

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

            $data = $apiDoc->toArray();

            if (isset($data['filters'])) {
                $parameters = array_merge($parameters, $this->deriveQueryParameters($data['filters']));
            }

            if (isset($data['parameters'])) {
                $parameters = array_merge($parameters, $this->deriveParameters($data['parameters'], $input['paramType']));
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

                if (isset($prop['type']['collection']) && $prop['type']['collection'] === true) {

                    /*
                     * Without alias:       Fully\Qualified\Class\Name[]
                     * With alias:          Fully\Qualified\Class\Name[alias]
                     */
                    $alias = $prop['type']['collectionName'];

                    $newName = sprintf('%s[%s]', $className, $alias);
                    $collId =
                        $this->registerModel(
                            $newName,
                            array(
                                $alias => array(
                                    'dataType'    => null,
                                    'subType'     => $className,
                                    'actualType'  => DataTypes::COLLECTION,
                                    'required'    => true,
                                    'readonly'    => true,
                                    'description' => null,
                                    'default'     => null,
                                    'children'    => $prop['model'][$alias]['children'],
                                )
                            ),
                            ''
                        );
                    $responseModel = array(
                        'code' => $statusCode,
                        'message' => $message,
                        'responseModel' => $collId
                    );
                } else {

                    $responseModel = array(
                        'code' => $statusCode,
                        'message' => $message,
                        'responseModel' => $this->registerModel($className, $prop['model'], ''),
                    );
                }
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

        $apiDeclaration['models'] = $this->modelRegistry->getModels();
        $this->modelRegistry->clear();

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
     * @param  array $input
     * @return array
     */
    protected function deriveQueryParameters(array $input)
    {
        $parameters = array();

        foreach ($input as $name => $prop) {
            if (!isset($prop['dataType'])) {
                $prop['dataType'] = 'string';
            }
            $parameters[] = array(
                'paramType' => 'query',
                'name' => $name,
                'type' => isset($this->typeMap[$prop['dataType']]) ? $this->typeMap[$prop['dataType']] : 'string',
                'description' => isset($prop['description']) ? $prop['description'] : null,
            );
        }

        return $parameters;

    }

    /**
     * Builds a Swagger-compliant parameter list from the provided parameter array. Models are built when necessary.
     *
     * @param array $input
     * @param array $models
     *
     * @param string $paramType
     *
     * @return array
     */
    protected function deriveParameters(array $input, $paramType = 'form')
    {

        $parameters = array();

        foreach ($input as $name => $prop) {

            $type = null;
            $format = null;
            $ref = null;
            $enum = null;
            $items = null;

            if (!isset($prop['actualType'])) {
                $prop['actualType'] = 'string';
            }

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
                                $prop['description'] ?: $prop['dataType']
                            );
                        break;

                    case DataTypes::COLLECTION:
                        $type = 'array';
                        if ($prop['subType'] === null) {
                            $items = array('type' => 'string');
                        } elseif (isset($this->typeMap[$prop['subType']])) {
                            $items = array('type' => $this->typeMap[$prop['subType']]);
                        } else {
                            $ref =
                                $this->registerModel(
                                    $prop['subType'],
                                    isset($prop['children']) ? $prop['children'] : null,
                                    $prop['description'] ?: $prop['dataType']
                                );
                            $items = array(
                                '$ref' => $ref,
                            );
                        }
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
                'paramType' => $paramType,
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

            if (isset($prop['default'])) {
                $parameter['defaultValue'] = $prop['default'];
            }

            if (isset($items)) {
                $parameter['items'] = $items;
            }

            if (isset($prop['description'])) {
                $parameter['description'] = $prop['description'];
            }

            $parameters[] = $parameter;
        }

        return $parameters;
    }

    /**
     * Registers a model into the model array. Returns a unique identifier for the model to be used in `$ref` properties.
     *
     * @param        $className
     * @param array  $parameters
     * @param string $description
     *
     * @internal param $models
     * @return mixed
     */
    public function registerModel($className, array $parameters = null, $description = '')
    {
        return $this->modelRegistry->register($className, $parameters, $description);
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
     * @param $basePath
     * @return mixed
     */
    protected function stripBasePath($basePath)
    {
        if ('/' === $this->basePath) {
            return $basePath;
        }

        $path = sprintf('#^%s#', preg_quote($this->basePath));
        $subPath = preg_replace($path, '', $basePath);

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
