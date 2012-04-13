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

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertFalse($annot->isResource());
        $this->assertNull($annot->getDescription());
        $this->assertNull($annot->getFormType());
    }

    public function testConstructWithInvalidData()
    {
        $data = array(
            'unknown'   => 'foo',
            'array'     => array('bar' => 'bar'),
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertFalse($annot->isResource());
        $this->assertNull($annot->getDescription());
        $this->assertNull($annot->getFormType());
    }

    public function testConstruct()
    {
        $data = array(
            'description' => 'Heya',
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertFalse($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertNull($annot->getFormType());
    }

    public function testConstructDefinesAFormType()
    {
        $data = array(
            'description'   => 'Heya',
            'formType'      => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertFalse($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertEquals($data['formType'], $annot->getFormType());
    }

    public function testConstructMethodIsResource()
    {
        $data = array(
            'resource'      => true,
            'description'   => 'Heya',
            'formType'      => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertTrue($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertEquals($data['formType'], $annot->getFormType());
    }

    public function testConstructMethodResourceIsFalse()
    {
        $data = array(
            'resource'      => false,
            'description'   => 'Heya',
            'formType'      => 'My\Form\Type',
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertFalse($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertEquals($data['formType'], $annot->getFormType());
    }

    public function testConstructMethodHasFilters()
    {
        $data = array(
            'resource'      => true,
            'description'   => 'Heya',
            'filters'       => array(
                array('name' => 'a-filter'),
            ),
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(1, $annot->getFilters());
        $this->assertEquals(array('a-filter' => array()), $annot->getFilters());
        $this->assertTrue($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertNull($annot->getFormType());
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
            'formType'      => 'My\Form\Type',
            'filters'       => array(
                array('name' => 'a-filter'),
            ),
        );

        $annot = new ApiDoc($data);

        $this->assertTrue(is_array($annot->getFilters()));
        $this->assertCount(0, $annot->getFilters());
        $this->assertEquals(array(), $annot->getFilters());
        $this->assertTrue($annot->isResource());
        $this->assertEquals($data['description'], $annot->getDescription());
        $this->assertEquals($data['formType'], $annot->getFormType());
    }
}
