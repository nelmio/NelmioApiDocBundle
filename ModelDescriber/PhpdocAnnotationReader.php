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

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Items;
use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;

/**
 * @internal
 */
class PhpdocAnnotationReader
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
     * @param \ReflectionProperty $reflectionProperty
     * @param Items|Schema        $property
     */
    public function updateWithPhpdoc(\ReflectionProperty $reflectionProperty, $property)
    {
        try {
            $docBlock = $this->docBlockFactory->create($reflectionProperty);
            if (!$title = $docBlock->getSummary()) {
                /** @var Var_ $var */
                foreach ($docBlock->getTagsByName('var') as $var) {
                    if (null === $description = $var->getDescription()) continue;
                    $title = $description->render();
                    if ($title) break;
                }
            }
            if ($property->getTitle() === null && $title) {
                $property->setTitle($title);
            }
            if ($property->getDescription() === null && $docBlock->getDescription()) {
                $property->setDescription($docBlock->getDescription()->render());
            }
        } catch (\Exception $e) {
            // ignore
        }
    }
}
