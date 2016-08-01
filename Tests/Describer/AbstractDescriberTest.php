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

use EXSyst\Component\Swagger\Swagger;

abstract class AbstractDescriberTest extends \PHPUnit_Framework_TestCase
{
    protected $describer;

    protected function getSwaggerDoc(): Swagger
    {
        $api = new Swagger();
        $this->describer->describe($api);

        return $api;
    }
}
