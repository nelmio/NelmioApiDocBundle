<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Annotation;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Tests\TestCase;

class ApiDocTest extends TestCase
{
    public function testConstructWithoutData()
    {
        $data = array();

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertFalse($annot->isResource());
        $this->assertFalse($annot->getDeprecated());
        $this->assertFalse(isset($array['description']));
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertNull($annot->getInput());
        $this->assertFalse($array['authentication']);
        $this->assertTrue(is_array($array['authenticationRoles']));
    }

    public function testConstructWithInvalidData()
    {
        $data = array(
            'unknown'   => 'foo',
            'array'     => array('bar' => 'bar'),
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertFalse($annot->isResource());
        $this->assertFalse($annot->getDeprecated());
        $this->assertFalse(isset($array['description']));
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertNull($annot->getInput());
    }

    public function testConstruct()
    {
        $data = array(
            'description' => 'Heya',
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertFalse($annot->isResource());
        $this->assertFalse($annot->getDeprecated());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertNull($annot->getInput());
    }

    public function testConstructDefinesAFormType()
    {
        $data = array(
            'description'   => 'Heya',
            'input'         => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertFalse($annot->isResource());
        $this->assertFalse($annot->getDeprecated());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertEquals($data['input'], $annot->getInput());
    }

    public function testConstructMethodIsResource()
    {
        $data = array(
            'resource'      => true,
            'description'   => 'Heya',
            'deprecated'    => true,
            'input'         => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertTrue($annot->isResource());
        $this->assertTrue($annot->getDeprecated());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertEquals($data['input'], $annot->getInput());
    }

    public function testConstructMethodResourceIsFalse()
    {
        $data = array(
            'resource'      => false,
            'description'   => 'Heya',
            'deprecated'    => false,
            'input'         => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertFalse($annot->isResource());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertEquals($data['deprecated'], $array['deprecated']);
        $this->assertEquals($data['input'], $annot->getInput());
    }

    public function testConstructMethodHasFilters()
    {
        $data = array(
            'resource'      => true,
            'deprecated'    => false,
            'description'   => 'Heya',
            'filters'       => array(
                array('name' => 'a-filter'),
            ),
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(is_array($array['filters']));
        $this->assertCount(1, $array['filters']);
        $this->assertEquals(array('a-filter' => array()), $array['filters']);
        $this->assertTrue($annot->isResource());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertFalse(isset($array['requirements']));
        $this->assertFalse(isset($array['parameters']));
        $this->assertEquals($data['deprecated'], $array['deprecated']);
        $this->assertNull($annot->getInput());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructMethodHasFiltersWithoutName()
    {
        $data = array(
            'description'   => 'Heya',
            'filters'       => array(
                array('parameter' => 'foo'),
            ),
        );

        $annot = new ApiDoc($data);
    }

    public function testConstructNoFiltersIfFormTypeDefined()
    {
        $data = array(
            'resource'      => true,
            'description'   => 'Heya',
            'input'         => 'My\Form\Type',
            'filters'       => array(
                array('name' => 'a-filter'),
            ),
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertFalse(isset($array['filters']));
        $this->assertTrue($annot->isResource());
        $this->assertEquals($data['description'], $array['description']);
        $this->assertEquals($data['input'], $annot->getInput());
    }

    public function testConstructWithStatusCodes()
    {
        $data = array(
            'description' => 'Heya',
            'statusCodes' => array(
                200 => "Returned when successful",
                403 => "Returned when the user is not authorized",
                404 => array(
                    "Returned when the user is not found",
                    "Returned when when something else is not found"
                )
            )
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(is_array($array['statusCodes']));
        foreach ($data['statusCodes'] as $code => $message) {
            $this->assertEquals($array['statusCodes'][$code], !is_array($message) ? array($message) : $message);
        }
    }

    public function testConstructWithAuthentication()
    {
        $data = array(
            'authentication' => true
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue($array['authentication']);
    }

    public function testConstructWithCache()
    {
        $data = array(
            'cache' => '60'
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertEquals($data['cache'], $array['cache']);
    }

    public function testConstructWithRequirements()
    {
        $data = array(
            'requirements' => array(
                array(
                    'name' => 'fooId',
                    'requirement' => '\d+',
                    'dataType' => 'integer',
                    'description' => 'This requirement might be used withing action method directly from Request object'
                )
            )
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(isset($array['requirements']['fooId']));
        $this->assertTrue(isset($array['requirements']['fooId']['dataType']));
    }

    public function testConstructWithParameters()
    {
        $data = array(
            'parameters' => array(
                array(
                    'name' => 'fooId',
                    'dataType' => 'integer',
                    'description' => 'Some description'
                )
            )
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(isset($array['parameters']['fooId']));
        $this->assertTrue(isset($array['parameters']['fooId']['dataType']));
    }

    public function testConstructWithOneTag()
    {
        $data = array(
            'tags' => 'beta'
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(is_array($array['tags']), 'Single tag should be put in array');
        $this->assertEquals(array('beta'), $array['tags']);
    }

    public function testConstructWithMultipleTags()
    {
        $data = array(
            'tags' => array(
                'experimental',
                'alpha'
            )
        );

        $annot = new ApiDoc($data);
        $array = $annot->toArray();

        $this->assertTrue(is_array($array));
        $this->assertTrue(is_array($array['tags']), 'Tags should be in array');
        $this->assertEquals($data['tags'], $array['tags']);
    }
}
