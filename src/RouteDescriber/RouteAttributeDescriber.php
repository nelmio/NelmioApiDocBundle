<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\RouteDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Route;

final class RouteAttributeDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
{
    use RouteDescriberTrait;
    use ModelRegistryAwareTrait;

    private ?array $securitySchemes;

    /**
     * @param string[]|null $securitySchemes
     */
    public function __construct(
        ?array $securitySchemes
    ) {
        $this->securitySchemes = $securitySchemes;
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        if (null === $this->securitySchemes) {
            return;
        }

        if (PHP_VERSION_ID < 80100) {
            return;
        }

        $attributes = $this->getAttributes($route->getDefault('_controller'));
    }

    private function getAttributes(string|object|array $controller): array
    {
        if (\is_array($controller) && method_exists(...$controller)) {
            $controllerReflector = new \ReflectionMethod(...$controller);
        } elseif (\is_string($controller) && str_contains($controller, '::')) {
            $controllerReflector = new \ReflectionMethod(...explode('::', $controller, 2));
        } else {
            $controllerReflector = new \ReflectionFunction($controller(...));
        }

        if (\is_array($controller) && method_exists(...$controller)) {
            $classReflector = new \ReflectionClass($controller[0]);
        } elseif (\is_string($controller) && false !== $i = strpos($controller, '::')) {
            $classReflector = new \ReflectionClass(substr($controller, 0, $i));
        } else {
            $classReflector = $controllerReflector instanceof \ReflectionFunction && $controllerReflector->isAnonymous() ? null : $controllerReflector->getClosureCalledClass();
        }

        $this->attributes = [];
        foreach (array_merge($classReflector?->getAttributes() ?? [], $controllerReflector->getAttributes()) as $attribute) {
            if (class_exists($attribute->getName())) {
                $this->attributes[] = $attribute->newInstance();
            }
        }

        return $this->attributes;
    }
}
