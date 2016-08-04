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

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Swagger;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Regex;

class FosRestDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    private $annotationReader;

    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    public function describe(Swagger $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->annotationReader->getMethodAnnotations();
        $annotations = array_filter($annotations, function ($value) {
            return $value instanceof RequestParam || $value instanceof QueryParam;
        });

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($annotations as $annotation) {
                $in = $annotation instanceof QueryParam ? 'query' : 'formData';
                $parameter = $operation->getParameters()->get($annotation->getKey(), $in);

                $parameter->setAllowEmptyValue($annotation->nullable && $annotation->allowBlank);
                $parameter->setType($annotation->map ? 'array' : 'string');
                $parameter->setDefault($annotation->getDefault());
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
