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
use OpenApi\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RouteSecurityDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
{
    use RouteDescriberTrait;
    use ModelRegistryAwareTrait;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $securitySchemes;

    /**
     * @param array<string, array<string, mixed>> $securitySchemes
     */
    public function __construct(array $securitySchemes)
    {
        $this->securitySchemes = $securitySchemes;
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        if (!class_exists(IsGranted::class)) {
            return;
        }

        if ([] === $this->securitySchemes) {
            return;
        }

        if (PHP_VERSION_ID < 80100) {
            return;
        }

        $controller = $route->getDefault('_controller');
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
