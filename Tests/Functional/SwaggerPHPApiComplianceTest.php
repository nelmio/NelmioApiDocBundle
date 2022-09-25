<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Analysis;
use OpenApi\Context;

class SwaggerPHPApiComplianceTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testAllContextsHaveSameRoot()
    {
        $openApi = $this->getOpenApiDefinition();
        $root = $openApi->_context;

        $counter = 0;
        foreach ((new Analysis([$openApi], Util::createContext()))->annotations as $annotation) {
            $this->assertSame($annotation->_context->root(), $root);
        }
    }
}
