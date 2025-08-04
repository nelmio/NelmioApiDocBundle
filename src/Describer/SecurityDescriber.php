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
            if (!$api->components instanceof OA\Components) {
                $api->components = new OA\Components(['_context' => Util::createWeakContext($api->_context)]);
            }

            Util::getCollectionItem(
                $api->components,
                OA\SecurityScheme::class,
                self::transformSecurityScheme($securityScheme) + ['securityScheme' => $name],
            );
        }

        foreach ($this->routeCollection->all() as $route) {
            $this->describeRoute($api, $route);
        }
    }

    private function describeRoute(OA\OpenApi $api, Route $route): void
    {
        $controller = $route->getDefault('_controller');
        if (null === $reflectionMethod = $this->controllerReflector->getReflectionMethod($controller)) {
            return;
        }

        $classReflector = null;
        try {
            if (\is_array($controller) && method_exists(...$controller)) {
                $classReflector = new \ReflectionClass($controller[0]);
            } elseif (\is_string($controller) && false !== $i = strpos($controller, '::')) {
                $classReflector = new \ReflectionClass(substr($controller, 0, $i));
            }
        } catch (\ReflectionException) {
            // Fallback
        }

        $attributes = array_map(
            static fn (\ReflectionAttribute $attribute): IsGranted => $attribute->newInstance(),
            array_merge($classReflector?->getAttributes(IsGranted::class) ?? [], $reflectionMethod->getAttributes(IsGranted::class)),
        );

        $scopes = array_map(
            static fn (IsGranted $attribute): string => $attribute->attribute,
            $attributes,
        );

        foreach ($this->getOperations($api, $route) as $operation) {
            if (!Generator::isDefault($operation->security)) {
                return;
            }

            $operation->security = [];
            foreach ($this->securitySchemes as $name => $securityScheme) {
                $operation->security[] = [$name => array_unique(array_values($scopes))];
            }
        }
    }

    /**
     * @param array<string, mixed> $securityScheme
     *
     * @return array<string, mixed>
     */
    private static function transformSecurityScheme(array $securityScheme): array
    {
        // Ensure Flow get transformed to the proper class variant
        if (isset($securityScheme['flows'])) {
            $flows = [];

            foreach ($securityScheme['flows'] as $flow => $flowProperties) {
                $flows[] = new OA\Flow($flowProperties + ['flow' => $flow]);
            }

            $securityScheme['flows'] = $flows;
        }

        return $securityScheme;
    }
}
