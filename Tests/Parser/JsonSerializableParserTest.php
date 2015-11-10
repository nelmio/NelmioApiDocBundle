<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Parser\JsonSerializableParser;

class JsonSerializableParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var JsonSerializableParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new JsonSerializableParser();
    }

    /**
     * @dataProvider dataTestParser
     */
    public function testParser($property, $expected)
    {
        $result = $this->parser->parse(array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JsonSerializableTest'));
        foreach ($expected as $name => $value) {
            $this->assertArrayHasKey($property, $result);
            $this->assertArrayHasKey($name, $result[$property]);
            $this->assertEquals($result[$property][$name], $expected[$name]);
        }
    }

    /**
     * @dataProvider dataTestSupports
     */
    public function testSupports($class, $expected)
    {
        $this->assertEquals($this->parser->supports(array('class' => $class)), $expected);
    }

    public function dataTestParser()
    {
        return array(
            array(
                'property' => 'id',
                'expected' => array(
                    'dataType' => 'integer',
                    'default'  => 123
                )
            ),
            array(
                'property' => 'name',
                'expected' => array(
                    'dataType' => 'string',
                    'default'  => 'My name',
                )
            ),
            array(
                'property' => 'child',
                'expected' => array(
                    'dataType' => 'object',
                    'children' => array(
                        'value' => array(
                            'dataType' => 'array',
                            'actualType' => 'array',
                            'subType' => null,
                            'required' => null,
                            'description' => null,
                            'readonly' => null,
                            'default' => null,
                        )
                    )
                )
            )
        );
    }

    public function dataTestSupports()
    {
        return array(
            array(
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JsonSerializableTest',
                'expected' => true
            ),
            array(
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JsonSerializableRequiredConstructorTest',
                'expected' => false
            ),
            array(
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JsonSerializableOptionalConstructorTest',
                'expected' => true
            ),
            array(
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Popo',
                'expected' => false
            )
        );
    }
}
