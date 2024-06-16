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

class IntegerPropertyDescriber implements PropertyDescriberInterface
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

        $property->type = 'integer';
    }

    public function supports(array $types): bool
    {
        return 1 === count($types) && Type::BUILTIN_TYPE_INT === $types[0]->getBuiltinType();
    }
}
