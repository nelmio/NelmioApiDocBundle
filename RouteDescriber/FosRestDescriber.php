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
                if ($annotation instanceof QueryParam) {
                    $parameter = $operation->getParameters()->get($annotation->getName(), 'query');
                    $parameter->setAllowEmptyValue($annotation->nullable && $annotation->allowBlank);

                    $parameter->setRequired(!$annotation->nullable && $annotation->strict);
                } else {
                    $body = $operation->getParameters()->get('body', 'body')->getSchema();
                    $body->setType('object');
                    $parameter = $body->getProperties()->get($annotation->getName());

                    if (!$annotation->nullable && $annotation->strict) {
                        $requiredParameters = $body->getRequired();
                        $requiredParameters[] = $annotation->getName();

                        $body->setRequired(array_values(array_unique($requiredParameters)));
                    }
                }

                $parameter->setDefault($annotation->getDefault());
                if (null === $parameter->getType()) {
                    $parameter->setType($annotation->map ? 'array' : 'string');
                }
                if (null === $parameter->getDescription()) {
                    $parameter->setDescription($annotation->description);
                }

                $pattern = $this->getPattern($annotation->requirements);
                if (null !== $pattern) {
                    $parameter->setPattern($pattern);
                }

                $format = $this->getFormat($annotation->requirements);
                if (null !== $format) {
                    $parameter->setFormat($format);
                }
            }
        }
    }

    private function getPattern($requirements)
    {
        if (is_array($requirements) && isset($requirements['rule'])) {
            return (string) $requirements['rule'];
        }

        if (is_string($requirements)) {
            return $requirements;
        }

        if ($requirements instanceof Regex) {
            return $requirements->getHtmlPattern();
        }

        return null;
    }

    private function getFormat($requirements)
    {
        if ($requirements instanceof Constraint && !$requirements instanceof Regex) {
            $reflectionClass = new \ReflectionClass($requirements);

            return $reflectionClass->getShortName();
        }

        return null;
    }
}
