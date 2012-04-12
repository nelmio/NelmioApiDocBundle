<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
use Nelmio\ApiBundle\Parser\FormTypeParser;
use Symfony\Component\Routing\Route;

abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * @var \Nelmio\ApiBundle\Parser\FormTypeParser
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
            $array[] = $this->getData($coll['annotation'], $coll['route']);
        }

        return $this->render($array);
    }

    protected abstract function renderOne(array $data);

    protected abstract function render(array $collection);

    private function getData(ApiDoc $apiDoc, Route $route)
    {
        $method = $route->getRequirement('_method');
        $data   = array(
            'method'        => $method,
            'uri'           => $route->compile()->getPattern(),
            'requirements'  => $route->compile()->getRequirements(),
        );

        unset($data['requirements']['_method']);

        if (null !== $formType = $apiDoc->getFormType()) {
            $data['parameters'] = $this->parser->parse(new $formType());

            if ('PUT' === $method) {
                // All parameters are optional with PUT (update)
                array_walk($data['parameters'], function($val, $key) use (&$data) {
                    $data['parameters'][$key]['is_required'] = false;
                });
            }
        }

        if ($filters = $apiDoc->getFilters()) {
            $data['filters'] = $filters;
        }

        if ($comment = $apiDoc->getComment()) {
            $data['comment'] = $comment;
        }

        return $data;
    }
}
