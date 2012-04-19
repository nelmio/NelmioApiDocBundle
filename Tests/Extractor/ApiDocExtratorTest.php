<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Extractor;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

class ApiDocExtractorTest extends WebTestCase
{
    public function testAll()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->all();

        $this->assertTrue(is_array($data));
        $this->assertCount(7, $data);

        foreach ($data as $d) {
            $this->assertTrue(is_array($d));
            $this->assertArrayHasKey('annotation', $d);
            $this->assertArrayHasKey('route', $d);
            $this->assertArrayHasKey('resource', $d);

            $this->assertInstanceOf('Nelmio\ApiDocBundle\Annotation\ApiDoc', $d['annotation']);
            $this->assertInstanceOf('Symfony\Component\Routing\Route', $d['route']);
            $this->assertNotNull($d['resource']);
        }

        $a1 = $data[0]['annotation'];
        $this->assertTrue($a1->isResource());
        $this->assertEquals('index action', $a1->getDescription());
        $this->assertTrue(is_array($a1->getFilters()));
        $this->assertNull($a1->getFormType());

        $a1 = $data[1]['annotation'];
        $this->assertTrue($a1->isResource());
        $this->assertEquals('index action', $a1->getDescription());
        $this->assertTrue(is_array($a1->getFilters()));
        $this->assertNull($a1->getFormType());

        $a2 = $data[2]['annotation'];
        $this->assertFalse($a2->isResource());
        $this->assertEquals('create test', $a2->getDescription());
        $this->assertTrue(is_array($a2->getFilters()));
        $this->assertEquals('Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType', $a2->getFormType());

        $a2 = $data[3]['annotation'];
        $this->assertFalse($a2->isResource());
        $this->assertEquals('create test', $a2->getDescription());
        $this->assertTrue(is_array($a2->getFilters()));
        $this->assertEquals('Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType', $a2->getFormType());
    }

    public function testGet()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');

        $this->assertTrue(isset($data['route']));
        $this->assertTrue(isset($data['annotation']));

        $a = $data['annotation'];
        $this->assertTrue($a->isResource());
        $this->assertEquals('index action', $a->getDescription());
        $this->assertTrue(is_array($a->getFilters()));
        $this->assertNull($a->getFormType());

        $data2 = $extractor->get('nemlio.test.controller:indexAction', 'test_service_route_1');
        $data2['route']->setDefault('_controller', $data['route']->getDefault('_controller'));
        $this->assertEquals($data, $data2);
    }

    public function testGetWithBadController()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Undefined\Controller::indexAction', 'test_route_1');

        $this->assertNull($data);

        $data = $extractor->get('undefined_service:index', 'test_service_route_1');

        $this->assertNull($data);
    }

    public function testGetWithBadRoute()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'invalid_route');

        $this->assertNull($data);

        $data = $extractor->get('nemlio.test.controller:indexAction', 'invalid_route');

        $this->assertNull($data);
    }

    public function testGetWithInvalidPattern()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController', 'test_route_1');

        $this->assertNull($data);

        $data = $extractor->get('nemlio.test.controller', 'test_service_route_1');

        $this->assertNull($data);
    }

    public function testGetWithMethodWithoutApiDocAnnotation()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::anotherAction', 'test_route_3');

        $this->assertNull($data);

        $data = $extractor->get('nemlio.test.controller:anotherAction', 'test_service_route_1');

        $this->assertNull($data);
    }

    public function testGetWithDocComment()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::myCommentedAction', 'test_route_5');

        $this->assertNotNull($data);
        $this->assertEquals(
            "This method is useful to test if the getDocComment works. And, it supports multilines until the first '@' char.",
            $data['annotation']->getDescription()
        );
    }
}
