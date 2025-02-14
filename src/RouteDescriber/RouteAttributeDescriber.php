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

final class RouteAttributeDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
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
        if (null === $this->securitySchemes) {
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
            $classReflector = $reflectionMethod instanceof \ReflectionFunction && $reflectionMethod->isAnonymous() ? null : $reflectionMethod->getClosureCalledClass();
        }

        $attributes = [];
        foreach (array_merge($classReflector?->getAttributes() ?? [], $reflectionMethod->getAttributes()) as $attribute) {
            if (class_exists($attribute->getName())) {
                $attributes[] = $attribute->newInstance();
            }
        }

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($attributes as $attribute) {
                if (class_exists(IsGranted::class) && $attribute instanceof IsGranted) {
                    $this->IsGranted($attribute, $operation);
                }
            }
        }
    }

    private function IsGranted(IsGranted $isGranted, OA\Operation $operation): void
    {
        if (!Generator::isDefault($operation->security)) {
            return;
        }

        if (!is_string($isGranted->attribute)) {
            return;
        }

        $operation->security = [];
        foreach ($this->securitySchemes as $name => $securityScheme) {
            if (!isset($operation->security[$securityScheme])) {
                $operation->security[][$securityScheme] = [];
            }

//            $operation->security[][$securityScheme][] = [$isGranted->attribute];
            $operation->security[0][$securityScheme] = array_merge($operation->security[0][$securityScheme], [$isGranted->attribute]);
        }
    }
}
