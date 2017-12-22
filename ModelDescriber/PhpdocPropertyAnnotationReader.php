<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use EXSyst\Component\Swagger\Items;
use EXSyst\Component\Swagger\Schema;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;

/**
 * Extract information about properties of a model from the DocBlock comment.
 *
 * @internal
 */
class PhpdocPropertyAnnotationReader
{
    private $docBlockFactory;

    public function __construct(DocBlockFactoryInterface $docBlockFactory = null)
    {
        if (null === $docBlockFactory) {
            $docBlockFactory = DocBlockFactory::createInstance();
        }
        $this->docBlockFactory = $docBlockFactory;
    }

    /**
     * Update the Swagger information with information from the DocBlock comment.
     *
     * @param \ReflectionProperty $reflectionProperty
     * @param Items|Schema        $property
     */
    public function updateWithPhpdoc(\ReflectionProperty $reflectionProperty, $property)
    {
        try {
            $docBlock = $this->docBlockFactory->create($reflectionProperty);
        } catch (\Exception $e) {
            // ignore
            return;
        }

        if (!$title = $docBlock->getSummary()) {
            /** @var Var_ $var */
            foreach ($docBlock->getTagsByName('var') as $var) {
                if (!$description = $var->getDescription()) {
                    continue;
                }
                $title = $description->render();
                if ($title) {
                    break;
                }
            }
        }
        if (null === $property->getTitle() && $title) {
            $property->setTitle($title);
        }
        if (null === $property->getDescription() && $docBlock->getDescription() && $docBlock->getDescription()->render()) {
            $property->setDescription($docBlock->getDescription()->render());
        }
    }
}
