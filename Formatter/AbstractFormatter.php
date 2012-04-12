<?php

namespace Nelmio\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Symfony\Component\Routing\Route;

abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * @var \Nelmio\ApiDocBundle\Parser\FormTypeParser
     */
    protected $parser;

    public function __construct(FormTypeParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $apiDoc, Route $route)
    {
        return $this->renderOne($this->getData($apiDoc, $route));
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $collection)
    {
        $array = array();
        foreach ($collection as $coll) {
            $resource = $coll['resource'];
            if (!isset($array[$resource])) {
                $array[$resource] = array();
            }

            $array[$resource][] = $this->getData($coll['annotation'], $coll['route']);
        }

        return $this->render($array);
    }

    /**
     * Format a single array of data
     *
     * @param array $data
     * @return string|array
     */
    protected abstract function renderOne(array $data);

    /**
     * Format a set of data for a given resource.
     *
     * @param string $resource      A resource name.
     * @param array $arrayOfData    A set of data.
     * @return string|array
     */
    protected abstract function renderResourceSection($resource, array $arrayOfData);

    /**
     * Format a set of resource sections.
     *
     * @param array $collection
     * @return string|array
     */
    protected abstract function render(array $collection);

    /**
     * @param ApiDoc $apiDoc
     * @param Route $route
     * @return array
     */
    protected function getData(ApiDoc $apiDoc, Route $route)
    {
        $method = $route->getRequirement('_method');
        $data   = array(
            'method'        => $method ?: 'ANY',
            'uri'           => $route->compile()->getPattern(),
            'requirements'  => $route->compile()->getRequirements(),
        );

        unset($data['requirements']['_method']);

        if (null !== $formType = $apiDoc->getFormType()) {
            $data['parameters'] = $this->parser->parse(new $formType());

            if ('PUT' === $method) {
                // All parameters are optional with PUT (update)
                array_walk($data['parameters'], function($val, $key) use (&$data) {
                    $data['parameters'][$key]['required'] = false;
                });
            }
        }

        if ($filters = $apiDoc->getFilters()) {
            $data['filters'] = $filters;
        }

        if ($description = $apiDoc->getDescription()) {
            $data['description'] = $description;
        }

        return $data;
    }
}
