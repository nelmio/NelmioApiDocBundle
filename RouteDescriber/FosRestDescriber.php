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
use Nelmio\ApiDocBundle\SwaggerPhp\Util;
use OpenApi\Annotations as OA;
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

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        $annotations = array_filter($annotations, static function ($value) {
            return $value instanceof RequestParam || $value instanceof QueryParam;
        });

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($annotations as $annotation) {
                $parameterName = $annotation->key ?? $annotation->getName(); // the key used by fosrest

                if ($annotation instanceof QueryParam) {
                    $name = $parameterName.($annotation->map ? '[]' : '');
                    $parameter = Util::getOperationParameter($operation, $name, 'query');
                    $parameter->allowEmptyValue = $annotation->nullable && $annotation->allowBlank;

                    $parameter->required = !$annotation->nullable && $annotation->strict;
                } else {
                    $bodyParameter = Util::getOperationParameter($operation, 'body', 'body');
                    /** @var OA\Schema $body */
                    $body = Util::getChild($bodyParameter, OA\Schema::class);
                    $body->type = 'object';
                    $parameter = Util::getProperty($body, $annotation->getName());

                    if (!$annotation->nullable && $annotation->strict) {
                        $requiredParameters = is_array($body->required) ? $body->required : [];
                        $requiredParameters[] = $annotation->getName();

                        $body->required = array_values(array_unique($requiredParameters));
                    }
                }

                $parameter->schema = Util::createChild($parameter, OA\Schema::class);
                $parameter->schema->default = $annotation->getDefault();

                if (OA\UNDEFINED === $parameter->schema->type) {
                    $parameter->schema->type = $annotation->map ? 'array' : 'string';
                }

                if (OA\UNDEFINED === $parameter->description) {
                    $parameter->description = $annotation->description;
                }

                if ($annotation->map) {
                    $parameter->type = 'array';
                    $parameter->collectionFormat = 'multi';
                    $parameter->items = Util::getChild($parameter, OA\Items::class);
                }

                $parameter->type = 'string';

                $pattern = $this->getPattern($annotation->requirements);
                if (null !== $pattern) {
                    $parameter->pattern = $pattern;
                }

                $format = $this->getFormat($annotation->requirements);
                if (null !== $format) {
                    $parameter->format = $format;
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
