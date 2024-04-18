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

use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

class BooleanPropertyDescriber implements PropertyDescriberInterface
{
    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, ?array $groups = null, ?OA\Schema $schema = null, array $context = [])
    {
        if (null === $schema) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.15.0',
                'Passing null for the $schema parameter of "PropertyDescriberInterface::describe()" is deprecated. In future versions, the $schema parameter will be made non-nullable',
            );
        }

        if (null !== $groups) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.17.0',
                'Using the $groups parameter of "PropertyDescriberInterface::describe()" is deprecated and will be removed in a future version. Pass groups via $context[\'groups\']',
            );
        }

        $property->type = 'boolean';
    }

    public function supports(array $types): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_BOOL === $types[0]->getBuiltinType();
    }
}
