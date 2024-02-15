<?php

namespace Nelmio\ApiDocBundle\Tests\Util;

use Nelmio\ApiDocBundle\Tests\Functional\Controller\BazingaController;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\DependencyInjection\Container;

class ControllerReflectorTest extends TestCase
{
    public function testGetReflectionMethod(): void
    {
        $controllerReflector = new ControllerReflector(new Container());
        $this->assertEquals(
            ReflectionMethod::class,
            $controllerReflector->getReflectionMethod([BazingaController::class, 'userAction']) !== null ? $controllerReflector->getReflectionMethod([BazingaController::class, 'userAction'])::class : self::class
        );
        $this->assertEquals(
            ReflectionMethod::class,
            $controllerReflector->getReflectionMethod(BazingaController::class.'::userAction') !== null ? $controllerReflector->getReflectionMethod(BazingaController::class.'::userAction')::class : self::class
        );
        $this->assertNull(
            $controllerReflector->getReflectionMethod('UnknownController::userAction')
        );
        $this->assertNull($controllerReflector->getReflectionMethod(null));
    }
}
