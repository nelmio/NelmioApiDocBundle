<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ArrayShapeType;

/**
 * @implements TypeDescriberInterface<ArrayShapeType>
 *
 * @internal
 */
final class ArrayShapeDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $schema->type = 'object';
        $required = [];

        foreach ($type->getShape() as $key => $shapeValue) {
            $property = Util::getProperty($schema, (string) $key);
            $this->describer->describe($shapeValue['type'], $property, $context);

            if (!$shapeValue['optional']) {
                $required[] = (string) $key;
            }
        }

        if ([] !== $required) {
            $schema->required = $required;
        }

        if (!$type->isSealed()) {
            $additionalProperties = Util::getChild($schema, OA\AdditionalProperties::class);
            $this->describer->describe($type->getExtraValueType(), $additionalProperties, $context);
        } else {
            $schema->additionalProperties = false;
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof ArrayShapeType;
    }
}
