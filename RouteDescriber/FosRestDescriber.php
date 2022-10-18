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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Regex;

final class FosRestDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    /** @var Reader */
    private $annotationReader;

    /** @var string[] */
    private $mediaTypes;

    public function __construct(Reader $annotationReader, array $mediaTypes)
    {
        $this->annotationReader = $annotationReader;
        $this->mediaTypes = $mediaTypes;
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod)
    {
        $annotations = $this->annotationReader->getMethodAnnotations($reflectionMethod);
        $annotations = array_filter($annotations, static function ($value) {
            return $value instanceof RequestParam || $value instanceof QueryParam;
        });
        $annotations = array_merge($annotations, $this->getAttributesAsAnnotation($reflectionMethod, RequestParam::class));
        $annotations = array_merge($annotations, $this->getAttributesAsAnnotation($reflectionMethod, QueryParam::class));

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($annotations as $annotation) {
                $parameterName = $annotation->key ?? $annotation->getName(); // the key used by fosrest

                if ($annotation instanceof QueryParam) {
                    $name = $parameterName.($annotation->map ? '[]' : '');
                    $parameter = Util::getOperationParameter($operation, $name, 'query');
                    $parameter->allowEmptyValue = $annotation->nullable && $annotation->allowBlank;

                    $parameter->required = !$annotation->nullable && $annotation->strict;

                    if (Generator::UNDEFINED === $parameter->description) {
                        $parameter->description = $annotation->description;
                    }

                    if ($annotation->map) {
                        $parameter->explode = true;
                    }

                    $schema = Util::getChild($parameter, OA\Schema::class);
                    $this->describeCommonSchemaFromAnnotation($schema, $annotation);
                } else {
                    /** @var OA\RequestBody $requestBody */
                    $requestBody = Util::getChild($operation, OA\RequestBody::class);
                    foreach ($this->mediaTypes as $mediaType) {
                        $contentSchema = $this->getContentSchemaForType($requestBody, $mediaType);
                        $schema = Util::getProperty($contentSchema, $parameterName);

                        if (!$annotation->nullable && $annotation->strict) {
                            $requiredParameters = is_array($contentSchema->required) ? $contentSchema->required : [];
                            $requiredParameters[] = $parameterName;

                            $contentSchema->required = array_values(array_unique($requiredParameters));
                        }
                        $this->describeCommonSchemaFromAnnotation($schema, $annotation);
                    }
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
            if ($requirements instanceof DateTime) {
                // As defined per RFC3339
                if (\DateTime::RFC3339 === $requirements->format || 'c' === $requirements->format) {
                    return 'date-time';
                }

                if ('Y-m-d' === $requirements->format) {
                    return 'date';
                }

                return null;
            }

            $reflectionClass = new \ReflectionClass($requirements);

            return $reflectionClass->getShortName();
        }

        return null;
    }

    private function getContentSchemaForType(OA\RequestBody $requestBody, string $type): OA\Schema
    {
        $requestBody->content = Generator::UNDEFINED !== $requestBody->content ? $requestBody->content : [];
        switch ($type) {
            case 'json':
                $contentType = 'application/json';

                break;
            case 'xml':
                $contentType = 'application/xml';

                break;
            default:
                throw new \InvalidArgumentException('Unsupported media type');
        }
        if (!isset($requestBody->content[$contentType])) {
            $requestBody->content[$contentType] = new OA\MediaType(
                [
                    'mediaType' => $contentType,
                ]
            );
            /** @var OA\Schema $schema */
            $schema = Util::getChild(
                $requestBody->content[$contentType],
                OA\Schema::class
            );
            $schema->type = 'object';
        }

        return Util::getChild(
            $requestBody->content[$contentType],
            OA\Schema::class
        );
    }

    private function describeCommonSchemaFromAnnotation(OA\Schema $schema, $annotation)
    {
        $schema->default = $annotation->getDefault();

        if (Generator::UNDEFINED === $schema->type) {
            $schema->type = $annotation->map ? 'array' : 'string';
        }

        if ($annotation->map) {
            $schema->type = 'array';
            $schema->items = Util::getChild($schema, OA\Items::class);
        }

        $pattern = $this->getPattern($annotation->requirements);
        if (null !== $pattern) {
            $schema->pattern = $pattern;
        }

        $format = $this->getFormat($annotation->requirements);
        if (null !== $format) {
            $schema->format = $format;
        }
    }

    /**
     * @return OA\AbstractAnnotation[]
     */
    private function getAttributesAsAnnotation(\ReflectionMethod $reflection, string $className): array
    {
        $annotations = [];
        if (\PHP_VERSION_ID < 80100) {
            return $annotations;
        }

        foreach ($reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $annotations[] = $attribute->newInstance();
        }

        return $annotations;
    }
}
