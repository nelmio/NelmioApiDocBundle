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

class SwaggerPHPApiComplianceTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testAllContextsCopyRoot(): void
    {
        $openApi = $this->getOpenApiDefinition();
        $root = $openApi->_context;
        self::assertTrue($root->is('version'));

        foreach ((new Analysis([$openApi], Util::createContext()))->annotations as $annotation) {
            self::assertSame($annotation->_context->version, $root->version);
        }
    }
}
