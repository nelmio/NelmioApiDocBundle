<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber\Annotations;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\PseudoTypes\IntegerRange;
use phpDocumentor\Reflection\PseudoTypes\NegativeInteger;
use phpDocumentor\Reflection\PseudoTypes\PositiveInteger;
use phpDocumentor\Reflection\Types\Compound;

/**
 * Extract information about properties of a model from the DocBlock comment.
 *
 * @internal
 */
class PropertyPhpDocReader
{
    private $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    /**
     * Update the Swagger information with information from the DocBlock comment.
     */
    public function updateProperty($reflection, OA\Property $property): void
    {
        try {
            $docBlock = $this->docBlockFactory->create($reflection);
        } catch (\Exception $e) {
            // ignore
            return;
        }

        $title = $docBlock->getSummary();

        /** @var Var_ $var */
        foreach ($docBlock->getTagsByName('var') as $var) {
            if (!$title && method_exists($var, 'getDescription') && $description = $var->getDescription()) {
                $title = $description->render();
            }

            if (
                (!isset($min) || null !== $min) && (!isset($max) || null !== $max)
                && method_exists($var, 'getType') && $type = $var->getType()
            ) {
                $types = $type instanceof Compound ? $type->getIterator() : [$type];

                foreach ($types as $type) {
                    if ($type instanceof IntegerRange) {
                        $min = is_numeric($type->getMinValue()) ? (int) $type->getMinValue() : null;
                        $max = is_numeric($type->getMaxValue()) ? (int) $type->getMaxValue() : null;
                        break;
                    } elseif ($type instanceof PositiveInteger) {
                        $min = 1;
                        $max = null;
                        break;
                    } elseif ($type instanceof NegativeInteger) {
                        $min = null;
                        $max = -1;
                        break;
                    }
                }
            }
        }

        if (Generator::UNDEFINED === $property->title && $title) {
            $property->title = $title;
        }
        if (Generator::UNDEFINED === $property->description && $docBlock->getDescription() && $docBlock->getDescription()->render()) {
            $property->description = $docBlock->getDescription()->render();
        }
        if (Generator::UNDEFINED === $property->minimum && isset($min)) {
            $property->minimum = $min;
        }
        if (Generator::UNDEFINED === $property->maximum && isset($max)) {
            $property->maximum = $max;
        }
    }
}
