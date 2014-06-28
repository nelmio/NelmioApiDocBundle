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


use Symfony\Component\HttpFoundation\Request;

/**
 * Extends SwaggerFormatter which takes into account the request's base URL when generating the documents for direct swagger-ui consumption.
 *
 * @author Bezalel Hermoso <bezalelhermoso@gmail.com>
 */
class RequestAwareSwaggerFormatter extends SwaggerFormatter
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;


    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param array $collection
     * @param string $resource
     * @return array
     */
    protected function produceApiDeclaration(array $collection, $resource)
    {
        $data = parent::produceApiDeclaration($collection, $resource);
        $data['basePath'] = $this->request->getBaseUrl() . $data['basePath'];
        return $data;
    }
} 