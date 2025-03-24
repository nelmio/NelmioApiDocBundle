<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;

final class CompoundPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    use ModelRegistryAwareTrait;
    use PropertyDescriberAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = []): void
    {
        $property->oneOf = Generator::UNDEFINED !== $property->oneOf ? $property->oneOf : [];

        foreach ($types as $type) {
            $property->oneOf[] = $schema = Util::createChild($property, OA\Schema::class, []);

            $this->propertyDescriber->describe([$type], $schema, $context);
        }
    }

    public function supports(array $types, array $context = []): bool
    {
        return \count($types) >= 2;
    }
}
