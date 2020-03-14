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
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractDescriberTest extends TestCase
{
    /** @var DescriberInterface */
    protected $describer;

    protected function getSwaggerDoc(): Swagger
    {
        $api = new Swagger();
        $this->describer->describe($api);

        return $api;
    }
}
