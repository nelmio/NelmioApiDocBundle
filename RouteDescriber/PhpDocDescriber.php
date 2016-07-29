<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\RouteDescriber;

use EXSyst\Swagger\Swagger;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Symfony\Component\Routing\Route;

class PhpDocDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    private $docBlockFactory;

    public function __construct(DocBlockFactoryInterface $docBlockFactory = null)
    {
        if (null === $docBlockFactory) {
            $docBlockFactory = DocBlockFactory::createInstance();
        }
        $this->docBlockFactory = $docBlockFactory;
    }

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $classDocBlock = null;
        $docBlock = null;

        try {
            $classDocBlock = $this->docBlockFactory->create($reflectionMethod->getDeclaringClass());
        } catch (\Exception $e) {
        }
        try {
            $docBlock = $this->docBlockFactory->create($reflectionMethod);
        } catch (\Exception $e) {
        }

        foreach ($this->getOperations($api, $route) as $operation) {
            if (null !== $docBlock) {
                $operation->setSummary($docBlock->getSummary());
                $operation->setDescription((string) $docBlock->getDescription());
                $operation->setDeprecated($operation->getDeprecated() || $docBlock->hasTag('deprecated'));
            }
            if (null !== $classDocBlock) {
                $operation->setDeprecated($operation->getDeprecated() || $classDocBlock->hasTag('deprecated'));
            }
        }
    }
}
