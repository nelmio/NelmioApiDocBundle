<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\SwaggerPhpDescriber;
use Nelmio\ApiDocBundle\Model\ModelRegistry;

class SwaggerPhpDescriberTest extends AbstractDescriberTest
{
    public function testDescribe()
    {
        $api = $this->getSwaggerDoc();
        $info = $api->getInfo();

        $this->assertEquals('My Awesome App', $info->getTitle());
        $this->assertEquals('1.3', $info->getVersion());
    }

    protected function setUp()
    {
        $this->describer = new SwaggerPhpDescriber(__DIR__.'/../Fixtures');
        $this->describer->setModelRegistry(new ModelRegistry([], new Swagger()));
    }
}
