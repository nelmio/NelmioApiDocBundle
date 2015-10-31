<?php

namespace Nelmio\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Swagger2\ExpandedDefinition;
use Nelmio\ApiDocBundle\Swagger2\Segment;

/**
 * Class Swagger2Formatter
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class Swagger2Formatter implements FormatterInterface
{
    protected $info = array();

    protected $consumes = array();

    protected $produces = array();

    protected $schemes = array();

    protected $basePath = array();

    public function __construct()
    {

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


            if ($definition->hasPath($url)) {
                $path = $definition->getPath($url);
            } else {
                $path = new Segment\Path($url);
                $definition->addPath($path);
            }

            $parameters = array();
            $responses = array();

            foreach ($compiled->getPathVariables() as $paramValue) {
                $parameter = new Segment\Parameter\Path($paramValue);
                $path->addParameter($parameter);
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
                    $description = is_array($statusMessages[$statusCode]) ? implode('; ', $statusMessages[$statusCode]) : $statusCode[$statusCode];
                } else {
                    $description = sprintf('See standard HTTP status code reason for %s', $statusCode);
                }

                $className = !empty($prop['type']['form_errors']) ? $prop['type']['class'] . '.ErrorResponse' : $prop['type']['class'];

                if (isset($prop['type']['collection']) && $prop['type']['collection'] === true) {

                    /*
                     * Without alias:       Fully\Qualified\Class\Name[]
                     * With alias:          Fully\Qualified\Class\Name[alias]
                     */
                    $alias = $prop['type']['collectionName'];

                    $newName = sprintf('%s[%s]', $className, $alias);
                    $collectionId =
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
                        'description' => $description,
                        'schema' => array(
                            'type' => 'array',
                            'items' => array(
                                '$ref' => '#/definitions/' . $collectionId,
                            )
                        )
                    );
                } else {

                    $responseModel = array(
                        'description' => $description,
                        'schema' => array(
                            '$ref' => $this->registerModel($className, $prop['model'], ''),
                        ),
                    );
                }
                $responses[$statusCode] = $responseModel;
            }

            $unmappedMessages = array_diff(array_keys($statusMessages), array_keys($responses));

            foreach ($unmappedMessages as $code) {
                $responses[$code] = array(
                    'description' => is_array($statusMessages[$code]) ? implode('; ', $statusMessages[$code]) : $statusMessages[$code],
                );
            }

            foreach ($apiDoc->getRoute()->getMethods() as $method) {
                $method = strtolower($method);
                $operation = array(
                    'summary' => $apiDoc->getDescription(),
                    'description' => $apiDoc->getDescription(),
                    'parameters' => $parameters,
                    'responses' => $responses,
                );
                $paths[$path][$method] = $operation;
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
     * @param array $basePath
     */
    public function setBasePath($basePath)
    {
        $this->basePath = $basePath;
    }

    /**
     * Strips the base path from a URL path.
     *
     * @param $path
     * @return mixed
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
