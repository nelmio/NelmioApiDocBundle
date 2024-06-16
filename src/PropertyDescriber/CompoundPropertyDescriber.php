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

class CompoundPropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, PropertyDescriberAwareInterface
{
    use ModelRegistryAwareTrait;
    use PropertyDescriberAwareTrait;

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, ?array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        if (null === $schema) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.15.0',
                '"%s()" will have a new "OA\Schema $schema" argument in a future version. Not defining it or passing null is deprecated',
                __METHOD__
            );
        }

        if (null !== $groups) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.17.0',
                'Using the $groups parameter of "%s()" is deprecated and will be removed in a future version. Pass groups via $context[\'groups\']',
                __METHOD__
            );
        }

        $property->oneOf = Generator::UNDEFINED !== $property->oneOf ? $property->oneOf : [];

        foreach ($types as $type) {
            $property->oneOf[] = $schema = Util::createChild($property, OA\Schema::class, []);

            $this->propertyDescriber->describe([$type], $schema, $groups, $schema, $context);
        }
    }

    public function supports(array $types): bool
    {
        return count($types) >= 2;
    }
}
