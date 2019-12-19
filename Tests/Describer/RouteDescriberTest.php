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
use Nelmio\ApiDocBundle\Describer\RouteDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RouteDescriberTest extends AbstractDescriberTest
{
    private $routes;

    private $routeDescriber;

    public function testIgnoreWhenNoController()
    {
        $this->routes->add('foo', new Route('foo'));
        $this->routeDescriber->expects($this->never())
            ->method('describe');

        $this->assertEquals((new Swagger())->toArray(), $this->getSwaggerDoc()->toArray());
    }

    protected function setUp()
    {
        $this->routeDescriber = $this->createMock(RouteDescriberInterface::class);
        $this->routes = new RouteCollection();
        $this->describer = new RouteDescriber(
            $this->routes,
            $this->createControllerReflector(),
            [$this->routeDescriber]
        );
    }

    protected function createControllerReflector(): ControllerReflector
    {
        if (class_exists(ControllerNameParser::class)) {
            return new ControllerReflector(
                new Container(),
                $this->createMock(ControllerNameParser::class)
            );
        }

        return new ControllerReflector(new Container());
    }
}
