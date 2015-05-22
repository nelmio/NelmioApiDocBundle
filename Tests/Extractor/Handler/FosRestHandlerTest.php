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
    /**
     * @group fosrest.queryparam
     */
    public function testGetWithQueryParamStrict()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithQueryParamStrictAction', 'test_route_15');

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

    /**
     * @group fosrest.queryparam
     */
    public function testGetWithQueryParam()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithQueryParamAction', 'test_route_8');

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

    /**
     * @group fosrest.queryparam
     */
    public function testGetWithQueryParamNoDefault()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithQueryParamNoDefaultAction', 'test_route_16');

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

    /**
     * @group fosrest.queryparam
     */
    public function testGetWithConstraintAsRequirements()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithConstraintAsRequirements', 'test_route_21');

        $this->assertNotNull($annotation);

        $filters = $annotation->getFilters();
        $this->assertCount(1, $filters);
        $this->assertArrayHasKey('mail', $filters);

        $filter = $filters['mail'];

        $this->assertArrayHasKey('requirement', $filter);
        $this->assertEquals($filter['requirement'], 'Email');
    }

    /**
     * @group fosrest.requestparam
     */
    public function testGetWithRequestParam()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithRequestParamAction', 'test_route_11');

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

    /**
     * @group fosrest.requestparam
     */
    public function testGetWithRequestParamNullable()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithNullableRequestParamAction', 'test_route_22');

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

    /**
     * @group fosrest.view
     */
    public function testViewWithNoSerializerGroups()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithViewAndNoSerializerGroups', 'test_route_view_no_groups');

        $this->assertNotNull($annotation);

        $output = $annotation->getOutput();
        $this->assertInternalType('string', $output);
    }

    /**
     * @group fosrest.view
     */
    public function testViewWithSerializerGroups()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithViewAndSerializerGroups', 'test_route_view_with_groups');

        $this->assertNotNull($annotation);

        $output = $annotation->getOutput();
        $this->assertInternalType('array', $output);

        $this->assertArrayHasKey('groups', $output);
        $this->assertCount(1, $output['groups']);
        $this->assertEquals($output['groups'][0], 'some-group');
    }

    /**
     * @group fosrest.view
     */
    public function testViewWithNoOutputClass()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithViewButNoOutputClass', 'test_route_view_no_class');

        $this->assertNotNull($annotation);

        $output = $annotation->getOutput();
        $this->assertInternalType('null', $output);
    }

    /**
     * @group fosrest.view
     */
    public function testViewWithGroupsInOutput()
    {
        $container = $this->getContainer();
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\FosRestController::zActionWithViewAndGroupsInOutput', 'test_route_view_with_groups_in_output');

        $this->assertNotNull($annotation);

        $output = $annotation->getOutput();
        $this->assertInternalType('array', $output);

        $this->assertArrayHasKey('groups', $output);
        $this->assertCount(1, $output['groups']);
        $this->assertEquals($output['groups'][0], 'some-other-group');
    }
}
