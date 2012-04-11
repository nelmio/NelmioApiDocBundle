<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
use Nelmio\ApiBundle\Parser\FormTypeParser;
use Symfony\Component\Routing\Route;

class ApiDocFormatter
{
    /**
     * @var \Nelmio\ApiBundle\Parser\FormTypeParser
     */
    protected $parser;

    public function __construct(FormTypeParser $parser)
    {
        $this->parser = $parser;
    }

    public function format(ApiDoc $apiDoc, Route $route)
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
