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

class FosRestHandlerTest extends WebTestCase
{

    public function testGetWithQueryParamStrict()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithQueryParamStrictAction', 'test_route_15');

        $this->assertNotNull($annotation);

        $requirements = $annotation->getRequirements();
        $this->assertCount(1, $requirements);
        $this->assertArrayHasKey('page', $requirements);

        $requirement = $requirements['page'];

        $this->assertArrayHasKey('requirement', $requirement);
        $this->assertEquals($requirement['requirement'], '\d+');

        $this->assertArrayHasKey('description', $requirement);
        $this->assertEquals($requirement['description'], 'Page of the overview.');

        $this->assertArrayHasKey('dataType', $requirement);

        $this->assertArrayNotHasKey('default', $requirement);
    }

    public function testGetWithQueryParam()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithQueryParamAction', 'test_route_8');

        $this->assertNotNull($annotation);

        $filters = $annotation->getFilters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('page', $filters);

        $filter = $filters['page'];

        $this->assertArrayHasKey('requirement', $filter);
        $this->assertEquals($filter['requirement'], '\d+');

        $this->assertArrayHasKey('description', $filter);
        $this->assertEquals($filter['description'], 'Page of the overview.');

        $this->assertArrayHasKey('default', $filter);
        $this->assertEquals($filter['default'], '1');
    }

    public function testGetWithQueryParamNoDefault()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithQueryParamNoDefaultAction', 'test_route_16');

        $this->assertNotNull($annotation);

        $filters = $annotation->getFilters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('page', $filters);

        $filter = $filters['page'];

        $this->assertArrayHasKey('requirement', $filter);
        $this->assertEquals($filter['requirement'], '\d+');

        $this->assertArrayHasKey('description', $filter);
        $this->assertEquals($filter['description'], 'Page of the overview.');

        $this->assertArrayNotHasKey('default', $filter);
    }

    public function testGetWithConstraintAsRequirements()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithConstraintAsRequirements', 'test_route_21');

        $this->assertNotNull($annotation);

        $filters = $annotation->getFilters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('mail', $filters);

        $filter = $filters['mail'];

        $this->assertArrayHasKey('requirement', $filter);
        $this->assertEquals($filter['requirement'], 'Email');
    }

    public function testGetWithRequestParam()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithRequestParamAction', 'test_route_11');

        $this->assertNotNull($annotation);

        $parameters = $annotation->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('param1', $parameters);

        $parameter = $parameters['param1'];

        $this->assertArrayHasKey('dataType', $parameter);
        $this->assertEquals($parameter['dataType'], 'string');

        $this->assertArrayHasKey('description', $parameter);
        $this->assertEquals($parameter['description'], 'Param1 description.');

        $this->assertArrayHasKey('required', $parameter);
        $this->assertEquals($parameter['required'], true);

        $this->assertArrayNotHasKey('default', $parameter);
    }

    public function testGetWithRequestParamNullable()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithNullableRequestParamAction', 'test_route_22');

        $this->assertNotNull($annotation);

        $parameters = $annotation->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('param1', $parameters);

        $parameter = $parameters['param1'];

        $this->assertArrayHasKey('dataType', $parameter);
        $this->assertEquals($parameter['dataType'], 'string');

        $this->assertArrayHasKey('description', $parameter);
        $this->assertEquals($parameter['description'], 'Param1 description.');

        $this->assertArrayHasKey('required', $parameter);
        $this->assertEquals($parameter['required'], false);

        $this->assertArrayNotHasKey('default', $parameter);
    }

    public function testPostWithArrayRequestParam()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zActionWithArrayRequestParamAction', 'test_route_26');

        $this->assertNotNull($annotation);

        $parameters = $annotation->getParameters();
        $this->assertCount(1, $parameters);
        $this->assertArrayHasKey('param1', $parameters);

        $parameter = $parameters['param1'];

        $this->assertArrayHasKey('dataType', $parameter);
        $this->assertEquals('string[]', $parameter['dataType']);
    }

    public function testWithRequestParamArrayRequirements()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::routeWithQueryParamArrayRequirementsAction', 'test_route_29');

        $this->assertNotNull($annotation);
        $filters = $annotation->getFilters();

        $this->assertArrayHasKey('param1', $filters);
        $this->assertArrayHasKey('requirement', $filters['param1']);
        $this->assertEquals('regexp', $filters['param1']['requirement']);
    }

    public function testWithRequestParamPlainArrayRequirements()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::routeWithQueryParamPlainArrayRequirementsAction', 'test_route_30');

        $this->assertNotNull($annotation);
        $filters = $annotation->getFilters();

        $this->assertArrayHasKey('param1', $filters);
        $this->assertArrayHasKey('requirement', $filters['param1']);
        $this->assertEquals('NotNull, NotBlank', $filters['param1']['requirement']);
    }
}
