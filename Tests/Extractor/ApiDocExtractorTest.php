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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

class ApiDocExtractorTest extends WebTestCase
{
    const ROUTES_QUANTITY = 33;

    public function testAll()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        set_error_handler(array($this, 'handleDeprecation'));
        $data = $extractor->all();
        restore_error_handler();

        $this->assertTrue(is_array($data));
        $this->assertCount(self::ROUTES_QUANTITY, $data);

        $cacheFile = $container->getParameter('kernel.cache_dir') . '/api-doc.cache';
        $this->assertFileExists($cacheFile);
        $this->assertEquals(file_get_contents($cacheFile), serialize($data));

        foreach ($data as $d) {
            $this->assertTrue(is_array($d));
            $this->assertArrayHasKey('annotation', $d);
            $this->assertArrayHasKey('resource', $d);

            $this->assertInstanceOf('Nelmio\ApiDocBundle\Annotation\ApiDoc', $d['annotation']);
            $this->assertInstanceOf('Symfony\Component\Routing\Route', $d['annotation']->getRoute());
            $this->assertNotNull($d['resource']);
        }

        $a1 = $data[7]['annotation'];
        $array1 = $a1->toArray();
        $this->assertTrue($a1->isResource());
        $this->assertEquals('index action', $a1->getDescription());
        $this->assertTrue(is_array($array1['filters']));
        $this->assertNull($a1->getInput());

        $a1 = $data[7]['annotation'];
        $array1 = $a1->toArray();
        $this->assertTrue($a1->isResource());
        $this->assertEquals('index action', $a1->getDescription());
        $this->assertTrue(is_array($array1['filters']));
        $this->assertNull($a1->getInput());

        $a2 = $data[8]['annotation'];
        $array2 = $a2->toArray();
        $this->assertFalse($a2->isResource());
        $this->assertEquals('create test', $a2->getDescription());
        $this->assertFalse(isset($array2['filters']));
        $this->assertEquals('Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType', $a2->getInput());

        $a2 = $data[9]['annotation'];
        $array2 = $a2->toArray();
        $this->assertFalse($a2->isResource());
        $this->assertEquals('create test', $a2->getDescription());
        $this->assertFalse(isset($array2['filters']));
        $this->assertEquals('Nelmio\ApiDocBundle\Tests\Fixtures\Form\TestType', $a2->getInput());

        $a4 = $data[11]['annotation'];
        $this->assertTrue($a4->isResource());
        $this->assertEquals('TestResource', $a4->getResource());

        $a3 = $data[20]['annotation'];
        $this->assertTrue($a3->getHttps());

    }

    public function testGet()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::indexAction', 'test_route_1');

        $this->assertInstanceOf('Nelmio\ApiDocBundle\Annotation\ApiDoc', $annotation);

        $this->assertTrue($annotation->isResource());
        $this->assertEquals('index action', $annotation->getDescription());

        $array = $annotation->toArray();
        $this->assertTrue(is_array($array['filters']));
        $this->assertNull($annotation->getInput());

        $annotation2 = $extractor->get('nelmio.test.controller:indexAction', 'test_service_route_1');
        $annotation2->getRoute()
            ->setDefault('_controller', $annotation->getRoute()->getDefault('_controller'))
            ->compile(); // compile as we changed a default value
        $this->assertEquals($annotation, $annotation2);
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

        $data = $extractor->get('nelmio.test.controller:indexAction', 'invalid_route');

        $this->assertNull($data);
    }

    public function testGetWithInvalidPattern()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController', 'test_route_1');

        $this->assertNull($data);

        $data = $extractor->get('nelmio.test.controller', 'test_service_route_1');

        $this->assertNull($data);
    }

    public function testGetWithMethodWithoutApiDocAnnotation()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $data = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::anotherAction', 'test_route_3');

        $this->assertNull($data);

        $data = $extractor->get('nelmio.test.controller:anotherAction', 'test_service_route_1');

        $this->assertNull($data);
    }

    public function testGetWithDocComment()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::myCommentedAction', 'test_route_5');

        $this->assertNotNull($annotation);
        $this->assertEquals(
            "This method is useful to test if the getDocComment works.",
            $annotation->getDescription()
        );

        $data = $annotation->toArray();
        $this->assertEquals(
            4,
            count($data['requirements'])
        );
        $this->assertEquals(
            'The param type',
            $data['requirements']['paramType']['description']
        );
        $this->assertEquals(
            'The param id',
            $data['requirements']['param']['description']
        );
    }

    public function testGetWithAuthentication()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::AuthenticatedAction', 'test_route_13');

        $this->assertNotNull($annotation);
        $this->assertTrue(
            $annotation->getAuthentication()
        );
        $this->assertContains('ROLE_USER', $annotation->getAuthenticationRoles());
        $this->assertContains('ROLE_FOOBAR', $annotation->getAuthenticationRoles());
        $this->assertCount(2, $annotation->getAuthenticationRoles());
    }

    public function testGetWithCache()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zCachedAction', 'test_route_23');

        $this->assertNotNull($annotation);
        $this->assertEquals(
            60,
            $annotation->getCache()
        );
    }

    public function testGetWithDeprecated()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::DeprecatedAction', 'test_route_14');

        $this->assertNotNull($annotation);
        $this->assertTrue(
            $annotation->getDeprecated()
        );
    }

    public function testOutputWithSelectedParsers()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zReturnSelectedParsersOutputAction', 'test_route_19');

        $this->assertNotNull($annotation);
        $output = $annotation->getOutput();

        $parsers = $output['parsers'];
        $this->assertEquals(
            "Nelmio\\ApiDocBundle\\Parser\\JmsMetadataParser",
            $parsers[0]
        );
        $this->assertEquals(
            "Nelmio\\ApiDocBundle\\Parser\\ValidationParser",
            $parsers[1]
        );
        $this->assertCount(2, $parsers);
    }

    public function testInputWithSelectedParsers()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zReturnSelectedParsersInputAction', 'test_route_20');

        $this->assertNotNull($annotation);
        $input = $annotation->getInput();
        $parsers = $input['parsers'];
        $this->assertEquals(
            "Nelmio\\ApiDocBundle\\Parser\\FormTypeParser",
            $parsers[0]
        );
        $this->assertCount(1, $parsers);
    }

    public function testPostRequestDoesRequireParametersWhenMarkedAsSuch()
    {
        $container  = $this->getContainer();
        /** @var ApiDocExtractor $extractor */
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        /** @var ApiDoc $annotation */
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::requiredParametersAction', 'test_required_parameters');

        $parameters = $annotation->getParameters();
        $this->assertTrue($parameters['required_field']['required']);
    }

    public function testPutRequestDoesNeverRequireParameters()
    {
        $container  = $this->getContainer();
        /** @var ApiDocExtractor $extractor */
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        /** @var ApiDoc $annotation */
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::requiredParametersAction', 'test_put_disables_required_parameters');

        $parameters = $annotation->getParameters();
        $this->assertFalse($parameters['required_field']['required']);
    }
}
