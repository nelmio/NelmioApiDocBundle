<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\OpenApiPhp;

use OpenApi\Assembler\AbstractAttributeTranslator;
use OpenApi\Spec as OAS;

/**
 * Gives a route method's spec attributes the operation of the route they describe.
 *
 * The route defines the operation, so a method without an explicit operation gets one for the
 * route's path and methods, and what is declared on the method nests into it. An explicit
 * operation describes the route it sits on: it takes the route's path when it declares none, and
 * is dropped when its path or method is not the route's, as with the classic attributes. One
 * declaring no method at all takes the route's.
 *
 * The controller class's own attributes are added to the method's, so a response or a parameter
 * declared on the class applies to every operation in it.
 *
 * @internal
 */
final class RouteOperationTranslator extends AbstractAttributeTranslator
{
    /**
     * @param string[] $methods    the route's HTTP methods, lowercase
     * @param object[] $classLevel the controller class's spec attributes, other than tags
     */
    public function __construct(
        private readonly string $path,
        private readonly array $methods,
        private readonly array $classLevel = [],
    ) {
    }

    public function translate(array $attributes, array $created, \ReflectionClass|\ReflectionMethod|\ReflectionProperty|\ReflectionParameter|\ReflectionClassConstant $reflector): array
    {
        $attributes = [...$attributes, ...$created];
        if (!$reflector instanceof \ReflectionMethod) {
            return $attributes;
        }

        $attributes = [...$this->classLevel, ...$attributes];

        $explicit = false;
        foreach ($attributes as $ii => $attribute) {
            if (!$attribute instanceof OAS\Operation || null !== $attribute->webhook) {
                continue;
            }

            $attribute->path ??= $this->path;
            if (null === $attribute->method && null === $attribute->methods) {
                $attribute->methods = $this->methods;
            }
            $methods = null !== $attribute->method ? [$attribute->method] : $attribute->methods;
            if ($attribute->path !== $this->path || [] === array_intersect($methods, $this->methods)) {
                unset($attributes[$ii]);

                continue;
            }

            $explicit = true;
        }

        if (!$explicit) {
            $attributes[] = (new OAS\Operation(path: $this->path, methods: $this->methods))->setReflector($reflector);
        }

        return array_values($attributes);
    }
}
