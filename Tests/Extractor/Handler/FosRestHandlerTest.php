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

}
