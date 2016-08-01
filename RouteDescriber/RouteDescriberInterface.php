<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\RouteDescriber;

use EXSyst\Component\Swagger\Swagger;
use Symfony\Component\Routing\Route;

interface RouteDescriberInterface
{
    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod);
}
