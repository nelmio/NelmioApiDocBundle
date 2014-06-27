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

use Nelmio\ApiDocBundle\DataTypes;
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

        $expected = array(
            '/tests' =>
            array(
                0 =>
                array(
                    'method' => 'GET',
                    'uri' => '/tests.{_format}',
                    'description' => 'index action',
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
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                1 =>
                array(
                    'method' => 'GET',
                    'uri' => '/tests.{_format}',
                    'description' => 'index action',
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
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                2 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests.{_format}',
                    'host' => 'api.test.dev',
                    'description' => 'create test',
                    'parameters' =>
                    array(
                        'a' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                        'b' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'c' => array(
                            'dataType' => 'boolean',
                            'actualType' => DataTypes::BOOLEAN,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'd' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'default' => "DefaultTest",
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                    ),
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                3 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests.{_format}',
                    'host' => 'api.test.dev',
                    'description' => 'create test',
                    'parameters' =>
                    array(
                        'a' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                        'b' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'c' => array(
                            'dataType' => 'boolean',
                            'actualType' => DataTypes::BOOLEAN,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'd' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'default' => "DefaultTest",
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                    ),
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
            ),
            'others' =>
            array(
                0 =>
                array(
                    'method' => 'POST',
                    'uri' => '/another-post',
                    'description' => 'create another test',
                    'parameters' =>
                    array(
                        'dependency_type' => array(
                            'dataType' => 'object (dependency_type)',
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'dependency_type',
                            'default' => null,
                            'required' => true,
                            'readonly' => false,
                            'description' => '',
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => true,
                                    'description' => 'A nice description',
                                    'readonly' => false,
                                ),
                            ),
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                1 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/any',
                    'description' => 'Action without HTTP verb',
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                2 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/any/{foo}',
                    'description' => 'Action without HTTP verb',
                    'requirements' =>
                    array(
                        'foo' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                3 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/authenticated',
                    'https' => false,
                    'authentication' => true,
                    'authenticationRoles' => array('ROLE_USER','ROLE_FOOBAR'),
                    'deprecated' => false,
                ),
                4 =>
                array(
                    'method' => 'POST',
                    'uri' => '/jms-input-test',
                    'description' => 'Testing JMS',
                    'parameters' =>
                    array(
                        'foo' =>
                        array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'bar' =>
                        array(
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'number' =>
                        array(
                            'dataType' => 'double',
                            'actualType' => DataTypes::FLOAT,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'arr' =>
                        array(
                            'dataType' => 'array',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'nested' =>
                        array(
                            'dataType' => 'object (JmsNested)',
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' =>
                            array(
                                'foo' =>
                                array(
                                    'dataType' => 'DateTime',
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'bar' =>
                                array(
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'baz',
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'baz' =>
                                array(
                                    'dataType' => 'array of integers',
                                    'actualType' => DataTypes::COLLECTION,
                                    'subType' => DataTypes::INTEGER,
                                    'default' => null,
                                    'required' => false,
                                    'description' => 'Epic description.

With multiple lines.',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'circular' =>
                                array(
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => DataTypes::MODEL,
                                    'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'parent' =>
                                array(
                                    'dataType' => 'object (JmsTest)',
                                    'actualType' => DataTypes::MODEL,
                                    'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest',
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                    'children' =>
                                    array(
                                        'foo' =>
                                        array(
                                            'dataType' => 'string',
                                            'actualType' => DataTypes::STRING,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'bar' =>
                                        array(
                                            'dataType' => 'DateTime',
                                            'actualType' => DataTypes::DATETIME,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => true,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'number' =>
                                        array(
                                            'dataType' => 'double',
                                            'actualType' => DataTypes::FLOAT,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'arr' =>
                                        array(
                                            'dataType' => 'array',
                                            'actualType' => DataTypes::COLLECTION,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'nested' =>
                                        array(
                                            'dataType' => 'object (JmsNested)',
                                            'actualType' => DataTypes::MODEL,
                                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'nested_array' =>
                                        array(
                                            'dataType' => 'array of objects (JmsNested)',
                                            'actualType' => DataTypes::COLLECTION,
                                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                    ),
                                ),
                                'since' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.2',
                                    'untilVersion' => null,
                                ),
                                'until' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => '0.3',
                                ),
                                'since_and_until' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.4',
                                    'untilVersion' => '0.5',
                                ),
                            ),
                        ),
                        'nested_array' =>
                        array(
                            'dataType' => 'array of objects (JmsNested)',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                5 =>
                array(
                    'method' => 'GET',
                    'uri' => '/jms-return-test',
                    'description' => 'Testing return',
                    'response' =>
                    array(
                        'dependency_type' => array(
                            'dataType' => 'object (dependency_type)',
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'dependency_type',
                            'default' => null,
                            'required' => true,
                            'readonly' => false,
                            'description' => '',
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => true,
                                    'description' => 'A nice description',
                                    'readonly' => false,
                                ),
                            ),
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                6 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/my-commented/{id}/{page}/{paramType}/{param}',
                    'description' => 'This method is useful to test if the getDocComment works.',
                    'documentation' => 'This method is useful to test if the getDocComment works.
And, it supports multilines until the first \'@\' char.',
                    'requirements' =>
                    array(
                        'id' =>
                        array(
                            'dataType' => 'int',
                            'description' => 'A nice comment',
                            'requirement' => '',
                        ),
                        'page' =>
                        array(
                            'dataType' => 'int',
                            'description' => '',
                            'requirement' => '',
                        ),
                        'paramType' =>
                        array (
                            'dataType' => 'int',
                            'description' => 'The param type',
                            'requirement' => '',
                        ),
                        'param' =>
                        array (
                            'dataType' => 'int',
                            'description' => 'The param id',
                            'requirement' => '',
                        ),
                    ),
                    'https' => false,
                    'description' => 'This method is useful to test if the getDocComment works.',
                    'documentation' => "This method is useful to test if the getDocComment works.\nAnd, it supports multilines until the first '@' char.",
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                7 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/return-nested-output',
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                    'response' =>
                    array (
                        'foo' =>
                        array (
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'bar' =>
                        array (
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => true,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'number' =>
                        array (
                            'dataType' => 'double',
                            'actualType' => DataTypes::FLOAT,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'arr' =>
                        array (
                            'dataType' => 'array',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'nested' =>
                        array (
                            'dataType' => 'object (JmsNested)',
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'children' =>
                            array (
                                'foo' =>
                                array (
                                    'dataType' => 'DateTime',
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => true,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'bar' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'baz',
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'baz' =>
                                array (
                                    'dataType' => 'array of integers',
                                    'actualType' => DataTypes::COLLECTION,
                                    'subType' => DataTypes::INTEGER,
                                    'default' => null,
                                    'required' => false,
                                    'description' => 'Epic description.

With multiple lines.',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'circular' =>
                                array (
                                    'dataType' => 'object (JmsNested)',
                                    'actualType' => DataTypes::MODEL,
                                    'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                ),
                                'parent' =>
                                array (
                                    'dataType' => 'object (JmsTest)',
                                    'actualType' => DataTypes::MODEL,
                                    'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsTest',
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => null,
                                    'children' =>
                                    array (
                                        'foo' =>
                                        array (
                                            'dataType' => 'string',
                                            'actualType' => DataTypes::STRING,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'bar' =>
                                        array (
                                            'dataType' => 'DateTime',
                                            'actualType' => DataTypes::DATETIME,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => true,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'number' =>
                                        array (
                                            'dataType' => 'double',
                                            'actualType' => DataTypes::FLOAT,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'arr' =>
                                        array (
                                            'dataType' => 'array',
                                            'actualType' => DataTypes::COLLECTION,
                                            'subType' => null,
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'nested' =>
                                        array (
                                            'dataType' => 'object (JmsNested)',
                                            'actualType' => DataTypes::MODEL,
                                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                        'nested_array' =>
                                        array (
                                            'dataType' => 'array of objects (JmsNested)',
                                            'actualType' => DataTypes::COLLECTION,
                                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                                            'default' => null,
                                            'required' => false,
                                            'description' => '',
                                            'readonly' => false,
                                            'sinceVersion' => null,
                                            'untilVersion' => null,
                                        ),
                                    ),
                                ),
                                'since' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.2',
                                    'untilVersion' => null,
                                ),
                                'until' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => null,
                                    'untilVersion' => '0.3',
                                ),
                                'since_and_until' =>
                                array (
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => false,
                                    'description' => '',
                                    'readonly' => false,
                                    'sinceVersion' => '0.4',
                                    'untilVersion' => '0.5',
                                ),
                            ),
                        ),
                        'nested_array' =>
                        array (
                            'dataType' => 'array of objects (JmsNested)',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested',
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                    ),
                ),
                8 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/secure-route',
                    'https' => true,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                9 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/yet-another/{id}',
                    'requirements' =>
                    array(
                        'id' =>
                        array(
                            'requirement' => '\\d+',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                10 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-deprecated-indicator',
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => true,
                ),
                11 =>
                array(
                    'method' => 'POST',
                    'uri' => '/z-action-with-nullable-request-param',
                    'parameters' =>
                    array(
                        'param1' =>
                        array(
                            'required' => false,
                            'dataType' => 'string',
                            'description' => 'Param1 description.',
                            'readonly' => false,
                            'actualType' => 'string',
                            'subType' => null,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                12 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-query-param',
                    'filters' =>
                    array(
                        'page' =>
                        array(
                            'requirement' => '\\d+',
                            'description' => 'Page of the overview.',
                            'default' => '1',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                13 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-query-param-no-default',
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
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                14 =>
                array(
                    'method' => 'GET',
                    'uri' => '/z-action-with-query-param-strict',
                    'requirements' =>
                    array (
                        'page' =>
                        array (
                            'requirement' => '\\d+',
                            'dataType' => '',
                            'description' => 'Page of the overview.',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                15 =>
                array(
                    'method' => 'POST',
                    'uri' => '/z-action-with-request-param',
                    'parameters' =>
                    array(
                        'param1' =>
                        array(
                            'required' => true,
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'description' => 'Param1 description.',
                            'readonly' => false,
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
                16 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/z-return-jms-and-validator-output',
                    'https' => false,
                    'authentication' => false,
                    'deprecated' => false,
                    'response' => array (
                        'bar' => array(
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => null,
                            'readonly' => null
                        ),
                        'number' => array(
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'objects' => array(
                            'dataType' => 'array of objects (Test)',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test',
                            'default' => null,
                            'readonly' => null,
                            'required' => null,
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'nelmio',
                                    'format' => '{length: min: foo}, {not blank}',
                                    'required' => true,
                                    'readonly' => null
                                ),
                                'b' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => null,
                                    'readonly' => null
                                )
                            )
                        ),
                        'related' => array(
                            'dataType' => 'object (Test)',
                            'readonly' => false,
                            'required' => false,
                            'description' => '',
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test',
                            'default' => null,
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'format' => '{length: min: foo}, {not blank}',
                                    'required' => true,
                                    'readonly' => null,
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'nelmio',
                                ),
                                'b' => array(
                                    'dataType' => 'DateTime',
                                    'required' => null,
                                    'readonly' => null,
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                )
                            )
                        )
                    ),
                    'authenticationRoles' => array(),
                ),
                17 =>
                array(
                    'method' => "ANY",
                    'uri' => "/z-return-selected-parsers-input",
                    'https' => false,
                    'authentication' => false,
                    'deprecated' => false,
                    'authenticationRoles' => array(),
                    'parameters' =>
                    array(
                        'a' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => 'A nice description',
                            'readonly' => false,
                        ),
                        'b' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'c' => array(
                            'dataType' => 'boolean',
                            'actualType' => DataTypes::BOOLEAN,
                            'subType' => null,
                            'default' => null,
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                        'd' => array(
                            'dataType' => 'string',
                            'actualType' => DataTypes::STRING,
                            'subType' => null,
                            'default' => null,
                            'default' => "DefaultTest",
                            'required' => true,
                            'description' => '',
                            'readonly' => false,
                        ),
                    )
                ),
                18 =>
                array(
                    'method' => "ANY",
                    'uri' => "/z-return-selected-parsers-output",
                    'https' => false,
                    'authentication' => false,
                    'deprecated' => false,
                    'response' => array (
                        'bar' => array(
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => null,
                            'readonly' => null
                        ),
                        'number' => array(
                            'dataType' => 'DateTime',
                            'actualType' => DataTypes::DATETIME,
                            'subType' => null,
                            'default' => null,
                            'required' => false,
                            'description' => '',
                            'readonly' => false,
                            'sinceVersion' => null,
                            'untilVersion' => null
                        ),
                        'objects' => array(
                            'dataType' => 'array of objects (Test)',
                            'actualType' => DataTypes::COLLECTION,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test',
                            'default' => null,
                            'readonly' => null,
                            'required' => null,
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'nelmio',
                                    'format' => '{length: min: foo}, {not blank}',
                                    'required' => true,
                                    'readonly' => null
                                ),
                                'b' => array(
                                    'dataType' => 'DateTime',
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                    'required' => null,
                                    'readonly' => null
                                )
                            )
                        ),
                        'related' => array(
                            'dataType' => 'object (Test)',
                            'readonly' => false,
                            'required' => false,
                            'description' => '',
                            'sinceVersion' => null,
                            'untilVersion' => null,
                            'actualType' => DataTypes::MODEL,
                            'subType' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test',
                            'default' => null,
                            'children' => array(
                                'a' => array(
                                    'dataType' => 'string',
                                    'format' => '{length: min: foo}, {not blank}',
                                    'required' => true,
                                    'readonly' => null,
                                    'actualType' => DataTypes::STRING,
                                    'subType' => null,
                                    'default' => 'nelmio',
                                ),
                                'b' => array(
                                    'dataType' => 'DateTime',
                                    'required' => null,
                                    'readonly' => null,
                                    'actualType' => DataTypes::DATETIME,
                                    'subType' => null,
                                    'default' => null,
                                )
                            )
                        )
                    ),
                    'authenticationRoles' => array(),
                )
            ),
            '/tests2' =>
            array(
                0 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests2.{_format}',
                    'description' => 'post test 2',
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
            ),
            '/tests2' =>
            array(
                0 =>
                array(
                    'method' => 'POST',
                    'uri' => '/tests2.{_format}',
                    'description' => 'post test 2',
                    'requirements' =>
                    array(
                        '_format' =>
                        array(
                            'requirement' => '',
                            'dataType' => '',
                            'description' => '',
                        ),
                    ),
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
                ),
            ),
            'TestResource' =>
            array(
                0 =>
                array(
                    'method' => 'ANY',
                    'uri' => '/named-resource',
                    'https' => false,
                    'authentication' => false,
                    'authenticationRoles' => array(),
                    'deprecated' => false,
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
            'authenticationRoles' => array(),
            'deprecated' => false,
        );

        $this->assertEquals($expected, $result);
    }
}
