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
        set_error_handler(array($this, 'handleDeprecation'));
        $data      = $extractor->all();
        restore_error_handler();
        $result    = $container->get('nelmio_api_doc.formatter.simple_formatter')->format($data);

        $expected = array (
            '/tests' =>
            array (
                0 =>
                array (
                    'method' => 'GET',
                    'uri' => '/tests.{_format}',
                    'description' => 'index action',
                    'filters' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'integer',
                        ),
                        'b' =>
                        array (
                            'dataType' => 'string',
                            'arbitrary' =>
                            array (
                                0 => 'arg1',
                                1 => 'arg2',
                            ),
                        ),
                    ),
                    'requirements' =>
                    array (
                        '_format' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                1 =>
                array (
                    'method' => 'GET',
                    'uri' => '/tests.{_format}',
                    'description' => 'index action',
                    'filters' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'integer',
                        ),
                        'b' =>
                        array (
                            'dataType' => 'string',
                            'arbitrary' =>
                            array (
                                0 => 'arg1',
                                1 => 'arg2',
                            ),
                        ),
                    ),
                    'requirements' =>
                    array (
                        '_format' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                2 =>
                array (
                    'method' => 'POST',
                    'uri' => '/tests.{_format}',
                    'description' => 'create test',
                    'parameters' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                        'b' =>
                        array (
                            'dataType' => 'string',
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'c' =>
                        array (
                            'dataType' => 'boolean',
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                    ),
                    'requirements' =>
                    array (
                        '_format' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                3 =>
                array (
                    'method' => 'POST',
                    'uri' => '/tests.{_format}',
                    'description' => 'create test',
                    'parameters' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                        'b' =>
                        array (
                            'dataType' => 'string',
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'c' =>
                        array (
                            'dataType' => 'boolean',
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                    ),
                    'requirements' =>
                    array (
                        '_format' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
            ),
            'others' =>
            array (
                0 =>
                array (
                    'method' => 'POST',
                    'uri' => '/another-post',
                    'description' => 'create another test',
                    'parameters' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                1 =>
                array (
                    'method' => 'ANY',
                    'uri' => '/any',
                    'description' => 'Action without HTTP verb',
                    'https' => false,
                    'authentication' => false,
                ),
                2 =>
                array (
                    'method' => 'ANY',
                    'uri' => '/any/{foo}',
                    'description' => 'Action without HTTP verb',
                    'requirements' =>
                    array (
                        'foo' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                3 =>
                array (
                    'method' => 'ANY',
                    'uri' => '/authenticated',
                    'https' => false,
                    'authentication' => true,
                ),
                4 =>
                array(
                    'method' => 'POST',
                    'uri' => '/jms-input-test',
                    'description' => 'Testing JMS',
                    'parameters' =>
                    array (
                        'foo' =>
                        array (
                            'dataType' => 'string',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => false,
                            'groups' => null,
                        ),
                        'bar' =>
                        array (
                            'dataType' => 'DateTime',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => true,
                            'groups' => null,
                        ),
                        'number' =>
                        array (
                            'dataType' => 'double',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => false,
                            'groups' => null,
                        ),
                        'arr' =>
                        array (
                            'dataType' => 'array',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => false,
                            'groups' => null,
                        ),
                        'nested' =>
                        array (
                            'dataType' => 'object (JmsNested)',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => false,
                            'groups' => null,
                            'children' =>
                            array (
                                'foo' =>
                                array (
                                    'dataType' => 'DateTime',
                                    'required' => false,
                                    'description' => 'No description.',
                                    'readonly' => true,
                                    'groups' => null,
                                ),
                                'bar' =>
                                array (
                                    'dataType' => 'string',
                                    'required' => false,
                                    'description' => 'No description.',
                                    'readonly' => false,
                                    'groups' => null,
                                ),
                                'baz' =>
                                array (
                                    'dataType' => 'array of integers',
                                    'required' => false,
                                    'description' => 'Epic description.

With multiple lines.',
                                    'readonly' => false,
                                    'groups' => null,
                                ),
                                'circular' =>
                                array (
                                    'dataType' => 'object (JmsNested)',
                                    'required' => false,
                                    'description' => 'No description.',
                                    'readonly' => false,
                                    'groups' => null,
                                ),
                                'parent' =>
                                array (
                                    'dataType' => 'object (JmsTest)',
                                    'required' => false,
                                    'description' => 'No description.',
                                    'readonly' => false,
                                    'groups' => null,
                                    'children' =>
                                    array (
                                        'foo' =>
                                        array (
                                            'dataType' => 'string',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => false,
                                            'groups' => null,
                                        ),
                                        'bar' =>
                                        array (
                                            'dataType' => 'DateTime',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => true,
                                            'groups' => null,
                                        ),
                                        'number' =>
                                        array (
                                            'dataType' => 'double',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => false,
                                            'groups' => null,
                                        ),
                                        'arr' =>
                                        array (
                                            'dataType' => 'array',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => false,
                                            'groups' => null,
                                        ),
                                        'nested' =>
                                        array (
                                            'dataType' => 'object (JmsNested)',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => false,
                                            'groups' => null,
                                        ),
                                        'nested_array' =>
                                        array (
                                            'dataType' => 'array of objects (JmsNested)',
                                            'required' => false,
                                            'description' => 'No description.',
                                            'readonly' => false,
                                            'groups' => null,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                        'nested_array' =>
                        array (
                            'dataType' => 'array of objects (JmsNested)',
                            'required' => false,
                            'description' => 'No description.',
                            'readonly' => false,
                            'groups' => null,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                5 =>
                array(
                    'method' => 'GET',
                    'uri' => '/jms-return-test',
                    'description' => 'Testing return',
                    'response' =>
                    array (
                        'a' =>
                        array (
                            'dataType' => 'string',
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                6 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/my-commented/{id}/{page}',
                    'description' => 'This method is useful to test if the getDocComment works.',
                    'documentation' => 'This method is useful to test if the getDocComment works.
And, it supports multilines until the first \'@\' char.',
                    'requirements' =>
                    array (
                        'id' =>
                        array (
                            'dataType' => 'int',
                            'description' => 'A nice comment',
                            'requirement' => '',
                        ),
                        'page' =>
                        array (
                            'dataType' => 'int',
                            'description' => '',
                            'requirement' => '',
                        ),
                    ),
                    'https' => false,
                    'description' => 'This method is useful to test if the getDocComment works.',
                    'documentation' => "This method is useful to test if the getDocComment works.\nAnd, it supports multilines until the first '@' char.",
                    'authentication' => false,
                ),
                7 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/secure-route',
                    'requirements' =>
                    array (
                        '_scheme' =>
                        array (
                            'requirement' => 'https',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => true,
                    'authentication' => false,
                ),
                8 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/yet-another/{id}',
                    'requirements' =>
                    array (
                        'id' =>
                        array (
                            'requirement' => '\\d+',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                9 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-query-param',
                    'filters' =>
                    array (
                        'page' =>
                        array (
                            'requirement' => '\\d+',
                            'description' => 'Page of the overview.',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
                10 =>
                array(
                    'method' => 'POST',
                    'uri' => '/z-action-with-request-param',
                    'parameters' =>
                    array (
                        'param1' =>
                        array (
                            'required' => true,
                            'dataType' => 'string',
                            'description' => 'Param1 description.',
                            'readonly' => false,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                ),
            ),
            '/tests2' =>
            array (
                0 =>
                array (
                    'method' => 'POST',
                    'uri' => '/tests2.{_format}',
                    'description' => 'post test 2',
                    'requirements' =>
                    array (
                        '_format' =>
                        array (
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
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
            'uri' => '/tests.{_format}',
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
            'requirements' => array(
                '_format' => array('dataType' => '', 'description' => '', 'requirement' => ''),
            ),
            'https' => false,
            'authentication' => false,
        );

        $this->assertEquals($expected, $result);
    }
}
