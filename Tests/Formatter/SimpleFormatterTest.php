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
            '/tests' =>
            array(
                0 =>
                array(
                    'method' => 'GET',
                    'uri' => '/tests',
                    'filters' =>
                    array(
                        'a' =>
                        array(
                            'dataType' => 'integer',
                        ),
                        'b' =>
                        array(
                            'dataType' => 'string',
                            'arbitrary' =>
                            array(
                                0 => 'arg1',
                                1 => 'arg2',
                            ),
                        ),
                    ),
                    'description' => 'index action',
                ),
                1 =>
                array(
                    'method' => 'GET',
                    'uri' => '/tests',
                    'filters' =>
                    array(
                        'a' =>
                        array(
                            'dataType' => 'integer',
                        ),
                        'b' =>
                        array(
                            'dataType' => 'string',
                            'arbitrary' =>
                            array(
                                0 => 'arg1',
                                1 => 'arg2',
                            ),
                        ),
                    ),
                    'description' => 'index action',
                ),
                2 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests',
                    'parameters' =>
                    array(
                        'a' =>
                        array(
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                        ),
                        'b' =>
                        array(
                            'dataType' => 'string',
                            'required' => false,
                            'description' => '',
                        ),
                        'c' =>
                        array(
                            'dataType' => 'boolean',
                            'required' => true,
                            'description' => '',
                        ),
                    ),
                    'description' => 'create test',
                ),
                3 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests',
                    'parameters' =>
                    array(
                        'a' =>
                        array(
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                        ),
                        'b' =>
                        array(
                            'dataType' => 'string',
                            'required' => false,
                            'description' => '',
                        ),
                        'c' =>
                        array(
                            'dataType' => 'boolean',
                            'required' => true,
                            'description' => '',
                        ),
                    ),
                    'description' => 'create test',
                ),
            ),
            'others' =>
            array(
                0 =>
                array(
                    'method' => 'POST',
                    'uri' => '/another-post',
                    'parameters' =>
                    array(
                        'a' =>
                        array(
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                        ),
                    ),
                    'description' => 'create another test',
                ),
                1 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/any',
                    'description' => 'Action without HTTP verb',
                ),
                2 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/any/{foo}',
                    'requirements' =>
                    array(
                        'foo' => array('type' => '', 'description' => '', 'requirement' => ''),
                    ),
                    'description' => 'Action without HTTP verb',
                ),
                3 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/my-commented/{id}/{page}',
                    'requirements' =>
                    array(
                        'id' => array('type' => 'int', 'description' => 'A nice comment', 'requirement' => ''),
                        'page' => array('type' => 'int', 'description' => '', 'requirement' => ''),
                    ),
                    'description' => 'This method is useful to test if the getDocComment works.',
                    'documentation' => "This method is useful to test if the getDocComment works.\nAnd, it supports multilines until the first '@' char."
                ),
                4 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/yet-another/{id}',
                    'requirements' =>
                    array(
                        'id' => array('type' => '', 'description' => '', 'requirement' => '\d+')
                    ),
                ),
                5 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-query-param',
                    'filters' =>
                    array(
                        'page' => array('description' => 'Page of the overview.', 'requirement' => '\d+')
                    ),
                ),
            ),
        );

        $this->assertEquals($expected, $result);
    }

    public function testFormatOne()
    {
        $container = $this->getContainer();

        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');
        $result     = $container->get('nelmio_api_doc.formatter.simple_formatter')->formatOne($annotation);

        $expected = array(
            'method' => 'GET',
            'uri' => '/tests',
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
