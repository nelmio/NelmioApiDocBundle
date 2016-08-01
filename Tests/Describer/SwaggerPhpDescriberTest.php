<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Describer;

use EXSyst\Bundle\ApiDocBundle\Describer\SwaggerPhpDescriber;
use EXSyst\Component\Swagger\Swagger;

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
    }
}
