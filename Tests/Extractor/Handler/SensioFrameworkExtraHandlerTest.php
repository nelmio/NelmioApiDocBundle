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

class SensioFrameworkExtraHandlerTest extends WebTestCase
{
    public function testCacheAnnotation()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zCachedAction', 'test_route_23');

        $this->assertNotNull($annotation);

        $this->assertSame(60, $annotation->getCache());
    }

    public function testSecurityAnnotation()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestController::zSecuredAction', 'test_route_24');

        $this->assertNotNull($annotation);

        $this->assertTrue($annotation->getAuthentication());
    }

    public function testSecurityClassAnnotation()
    {
        $container  = $this->getContainer();
        $extractor  = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $annotation = $extractor->get('Nelmio\ApiDocBundle\Tests\Fixtures\Controller\TestSecuredController::indexAction', 'test_route_31');

        $this->assertNotNull($annotation);

        $this->assertTrue($annotation->getAuthentication());

        $this->assertContains('ROLE_USER', $annotation->getAuthenticationRoles());
        $this->assertContains('ROLE_FOOBAR', $annotation->getAuthenticationRoles());
        $this->assertCount(2, $annotation->getAuthenticationRoles());
    }
}
