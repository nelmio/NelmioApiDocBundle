<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberTrait;
use Nelmio\ApiDocBundle\Util\ControllerReflector;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class SecurityDescriber implements DescriberInterface
{
    use RouteDescriberTrait;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $securitySchemes;
    private RouteCollection $routeCollection;
    private ControllerReflector $controllerReflector;

    /**
     * @param array<string, array<string, mixed>> $securitySchemes
     */
    public function __construct(
        array $securitySchemes,
        RouteCollection $routeCollection,
        ControllerReflector $controllerReflector
    ) {
        $this->securitySchemes = $securitySchemes;
        $this->routeCollection = $routeCollection;
        $this->controllerReflector = $controllerReflector;
    }

    public function describe(OA\OpenApi $api): void
    {
        if (!class_exists(IsGranted::class)) {
            return;
        }

        if ([] === $this->securitySchemes) {
            return;
        }

        foreach ($this->securitySchemes as $name => $securityScheme) {
            Util::getCollectionItem(
                $api->components,
                OA\SecurityScheme::class,
                $securityScheme + ['securityScheme' => $name],
            );
        }

        foreach ($this->routeCollection->all() as $route) {
            if (!$route->hasDefault('_controller')) {
                continue;
            }

            $this->describeRoute($api, $route);
        }
    }

    private function describeRoute(OA\OpenApi $api, Route $route): void
    {
        $controller = $route->getDefault('_controller');
        if (null === $reflectionMethod = $this->controllerReflector->getReflectionMethod($controller)) {
            return;
        }

        if (\is_array($controller) && method_exists(...$controller)) {
            $classReflector = new \ReflectionClass($controller[0]);
        } elseif (\is_string($controller) && false !== $i = strpos($controller, '::')) {
            $classReflector = new \ReflectionClass(substr($controller, 0, $i));
        } else {
            return;
        }

        $attributes = array_map(
            static fn (\ReflectionAttribute $attribute): IsGranted => $attribute->newInstance(),
            array_merge($classReflector->getAttributes(IsGranted::class), $reflectionMethod->getAttributes(IsGranted::class)),
        );

        foreach ($this->getOperations($api, $route) as $operation) {
            if (!Generator::isDefault($operation->security)) {
                return;
            }

            $operation->security = [];

            $scopes = array_map(
                static fn (IsGranted $attribute): string => $attribute->attribute,
                $attributes,
            );

            foreach ($this->securitySchemes as $name => $securityScheme) {
                $operation->security[] = [$name => array_unique(array_values($scopes))];
            }
        }
    }
}
