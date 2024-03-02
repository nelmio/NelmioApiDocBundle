<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\RouteDescriber;

use OpenApi\Annotations as OA;
use OpenApi\Generator;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Symfony\Component\Routing\Route;

final class PhpDocDescriber implements RouteDescriberInterface
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

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod)
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
                if (Generator::UNDEFINED === $operation->summary && '' !== $docBlock->getSummary()) {
                    $operation->summary = $docBlock->getSummary();
                }
                if (Generator::UNDEFINED === $operation->description && '' !== (string) $docBlock->getDescription()) {
                    $operation->description = (string) $docBlock->getDescription();
                }
                if ($docBlock->hasTag('deprecated')) {
                    $operation->deprecated = true;
                }
            }
            if (null !== $classDocBlock) {
                if ($classDocBlock->hasTag('deprecated')) {
                    $operation->deprecated = true;
                }
            }
        }
    }
}
