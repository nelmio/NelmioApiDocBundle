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

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Swagger;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;

final class FosRestDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        $annotations = array_filter($annotations, function ($value) {
            return $value instanceof RequestParam || $value instanceof QueryParam;
        });

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($annotations as $annotation) {
                $in = $annotation instanceof QueryParam ? 'query' : 'formData';
                $parameter = $operation->getParameters()->get($annotation->getName(), $in);

                $parameter->setRequired(!$annotation->nullable && $annotation->strict);
                $parameter->setAllowEmptyValue($annotation->nullable && $annotation->allowBlank);
                $parameter->setDefault($annotation->getDefault());
                if (null === $parameter->getType()) {
                    $parameter->setType($annotation->map ? 'array' : 'string');
                }
                if (null === $parameter->getDescription()) {
                    $parameter->setDescription($annotation->description);
                }

                $normalizedRequirements = $this->normalizeRequirements($annotation->requirements);
                if (null !== $normalizedRequirements) {
                    $parameter->setFormat($normalizedRequirements);
                }
            }
        }
    }

    private function normalizeRequirements($requirements)
    {
        // if pattern
        if (isset($requirements['rule'])) {
            return (string) $requirements['rule'];
        }
        if (is_string($requirements)) {
            return $requirements;
        }
        // if custom constraint
        if ($requirements instanceof Constraint) {
            if ($requirements instanceof Regex) {
                return $requirements->getHtmlPattern();
            }

            $reflectionClass = new \ReflectionClass($requirements);

            return $reflectionClass->getShortName();
        }
    }
}
