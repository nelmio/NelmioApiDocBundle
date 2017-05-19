<?php

namespace Nelmio\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Swagger2\TypeMap;
use Nelmio\ApiDocBundle\Swagger2\ExpandedDefinition;
use Nelmio\ApiDocBundle\Swagger2\SchemaRegistry;
use Nelmio\ApiDocBundle\Swagger2\Segment;

/**
 * Class Swagger2Formatter
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class Swagger2Formatter implements FormatterInterface
{
    /**
     * @var array
     */
    protected $info = array();

    /**
     * @var array
     */
    protected $consumes = array();

    /**
     * @var array
     */
    protected $produces = array();

    /**
     * @var array
     */
    protected $schemes = array();

    /**
     * @var string
     */
    protected $basePath = array();

    /**
     * @var SchemaRegistry
     */
    protected $schemaRegistry;

    public function __construct()
    {
        $this->schemaRegistry = new SchemaRegistry();
    }

    /**
     * Format a collection of documentation data.
     *
     * @param  array [ApiDoc] $collection
     * @return string|array
     */
    public function format(array $collection)
    {
        $definition = new ExpandedDefinition(
            $this->basePath,
            $this->info,
            $this->schemes,
            $this->consumes,
            $this->produces
        );

        $paths = array();

        foreach ($collection as $item) {

            /** @var $apiDoc ApiDoc */
            $apiDoc = $item['annotation'];
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

            $compiled = $route->compile();

            $url = $this->stripBasePath($route->getPath());

            $path = new Segment\Path($url);

            $responses = array();

            foreach ($compiled->getPathVariables() as $paramValue) {
                $parameter = new Segment\Parameter\Path($paramValue);

                if ($paramValue === '_format' && false != ($req = $route->getRequirement('_format'))) {
                    $parameter->setEnum(explode('|', $req));
                }

                $path->addParameter($parameter);
            }

            $definition->addPath($path);

            $path->setMethods($route->getMethods());
            $path->setDescription($apiDoc->getDescription());

            $data = $apiDoc->toArray();

            if (isset($data['filters'])) {
                foreach ($data['filters'] as $name => $filter) {

                    $filter = array_merge(array(
                        'dataType' => 'string',
                        'actualType' => 'string',
                        'description' => null,
                    ), $filter);

                    $queryParameter = new Segment\Parameter\Query($name);

                    $type = TypeMap::type($filter['actualType'], 'string');

                    $queryParameter->setType($type);
                    $queryParameter->setDescription($filter['description']);
                    $path->addParameter($queryParameter);
                }
            }

            if (isset($data['parameters'])) {

                $identifier = $input['class'];

                if ($identifier) {
                    $body = new Segment\Parameter\Body("__body__");
                    $schema = $this->registerSchema($identifier, $data['parameters']);
                    if ($schema) {
                        $body->setSchema($schema);
                        $path->addParameter($body);
                    }
                } elseif ($this->inputIsOneDimensional($data['parameters'])) {
                    foreach ($data["parameters"] as $paramDefinition) {
                        $formParam = new Segment\Parameter\FormData($name);
                        $formParam->setType(TypeMap::type($paramDefinition['actualType']));
                        $path->addParameter($formParam);
                    }
                }
            }

        }

        return $definition->toArray();

    }

    public function inputIsOneDimensional(array $params)
    {
        $withChildren = array_map(function ($param) {
            return isset($param['children']) && count($param['children']) > 0;
        }, $params);

        return count($withChildren) > 0;
    }

    private function registerSchema($identifier, $parameters = null, $type = 'object')
    {
        if (!is_array($parameters)) {
            return null;
        }

        return $this->schemaRegistry->register($identifier, $parameters);
    }

    private function handleParameters(ExpandedDefinition $definition, array $input, $type)
    {
        $defaults = array(
            "actualType" => "string",
        );

        $format = null;
        $type = null;
        $reference = null;
        $enum = null;
        $items = null;

        foreach ($input as $paramName => $paramDefinition) {

            $paramDefinition = array_merge($defaults, $paramDefinition);

            if (isset($this->typeMap[$paramDefinition["actualType"]])) {
                $type = $this->typeMap[$paramDefinition["actualType"]];
            } else {

            }

        }
    }

    /**
     * Format documentation data for one route.
     *
     * @param ApiDoc $annotation
     *                           return string|array
     */
    public function formatOne(ApiDoc $annotation)
    {
        throw new \BadMethodCallException(sprintf('%s does not support formatting a single ApiDoc only.', __CLASS__));
    }

    /**
     * @param array $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
    }

    /**
     * @param array $consumes
     */
    public function setConsumes($consumes)
    {
        $this->consumes = $consumes;
    }

    /**
     * @param array $produces
     */
    public function setProduces($produces)
    {
        $this->produces = $produces;
    }

    /**
     * @param array $schemes
     */
    public function setSchemes($schemes)
    {
        $this->schemes = $schemes;
    }

    /**
     * @param string $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Strips the base path from a URL path.
     *
     * @param string $path
     * @return string
     */
    protected function stripBasePath($path)
    {
        if ('/' === $this->basePath) {
            return $path;
        }

        $pattern = sprintf('#^%s#', preg_quote($this->basePath));
        $subPath = preg_replace($pattern, '', $path);

        return $subPath;
    }

    protected function deriveQueryParameters(array $queryParams)
    {
        return array();
    }

    protected function deriveParameters(array $params)
    {
        return array();
    }
}
