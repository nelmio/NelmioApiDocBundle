<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Formatter;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

class SimpleFormatterTest extends WebTestCase
{
    public function testFormat()
    {
        $container = $this->getContainer();

        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data      = $extractor->all();
        $result    = $container->get('nelmio_api_doc.formatter.simple_formatter')->format($data);

        $expected = array(
            'others' => array(
                array(
                    'method' => 'ANY',
                    'uri' => '/any',
                    'requirements' => array(),
                    'description' => 'Action without HTTP verb'
                )
            ),
            '/tests' => array(
                array(
                    'method' => 'GET',
                    'uri' => '/tests',
                    'requirements' => array(),
                    'filters' => array(
                        'a' => array(
                            'dataType' => 'integer',
                        ),
                        'b' => array(
                            'dataType' => 'string',
                            'arbitrary' => array(
                                'arg1',
                                'arg2',
                            ),
                        ),
                    ),
                    'description' => 'index action',
                ),
                array(
                    'method' => 'POST',
                    'uri' => '/tests',
                    'requirements' => array(),
                    'parameters' => array(
                        'a' => array(
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                        ),
                        'b' => array(
                            'dataType' => 'string',
                            'required' => true,
                            'description' => '',
                        ),
                    ),
                    'description' => 'create test',
                ),
            ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testFormatOne()
    {
        $container = $this->getContainer();

        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data      = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');
        $result    = $container->get('nelmio_api_doc.formatter.simple_formatter')->formatOne($data['annotation'], $data['route']);

        $expected = array(
            'method' => 'GET',
            'uri' => '/tests',
            'requirements' => array(),
            'filters' => array(
                'a' => array(
                    'dataType' => 'integer',
                ),
                'b' => array(
                    'dataType' => 'string',
                    'arbitrary' => array(
                        'arg1',
                        'arg2',
                    ),
                ),
            ),
            'description' => 'index action'
        );

        $this->assertEquals($expected, $result);
    }
}
