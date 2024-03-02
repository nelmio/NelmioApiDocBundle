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

        if (!$title = $docBlock->getSummary()) {
            /** @var Var_ $var */
            foreach ($docBlock->getTagsByName('var') as $var) {
                if (!method_exists($var, 'getDescription') || !$description = $var->getDescription()) {
                    continue;
                }
                $title = $description->render();
                if ($title) {
                    break;
                }
            }
        }
        if (Generator::UNDEFINED === $property->title && $title) {
            $property->title = $title;
        }
        if (Generator::UNDEFINED === $property->description && $docBlock->getDescription() && $docBlock->getDescription()->render()) {
            $property->description = $docBlock->getDescription()->render();
        }
    }
}
