<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Controller;

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Controller\DocumentationController;
use Nelmio\ApiDocBundle\Controller\SwaggerUiController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ControllersTest extends TestCase
{
    /**
     * @group legacy
     * @expectedDeprecation Providing an instance of "Nelmio\ApiDocBundle\ApiDocGenerator" to "Nelmio\ApiDocBundle\Controller\SwaggerUiController::__construct()" is deprecated since version 3.1. Provide it an instance of "Psr\Container\ContainerInterface" instead.
     */
    public function testSwaggerUiControllerInstanciation()
    {
        if (class_exists('Twig_Environment')) {
            $twigMock = $this->createMock('Twig_Environment');
        } else {
            $twigMock = $this->createMock('Twig\Environment');
        }

        $controller = new SwaggerUiController(new ApiDocGenerator([], []), $twigMock);
        $controller(new Request());
    }

    /**
     * @group legacy
     * @expectedDeprecation Providing an instance of "Nelmio\ApiDocBundle\ApiDocGenerator" to "Nelmio\ApiDocBundle\Controller\DocumentationController::__construct()" is deprecated since version 3.1. Provide it an instance of "Psr\Container\ContainerInterface" instead.
     */
    public function testDocumentationControllerInstanciation()
    {
        $controller = new DocumentationController(new ApiDocGenerator([], []));
        $controller(new Request());
    }
}
