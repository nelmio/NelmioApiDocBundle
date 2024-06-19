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
use FOS\RestBundle\Controller\Annotations\AbstractScalarParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\Regex;

final class FosRestDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    private ?Reader $annotationReader;

    /** @var string[] */
    private array $mediaTypes;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(?Reader $annotationReader, array $mediaTypes)
    {
        $this->annotationReader = $annotationReader;
        $this->mediaTypes = $mediaTypes;
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $annotations = null !== $this->annotationReader
            ? $this->annotationReader->getMethodAnnotations($reflectionMethod)
            : [];
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

    /**
     * @param mixed $requirements Value to retrieve a pattern from
     */
    private function getPattern($requirements): ?string
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

    /**
     * @param mixed $requirements Value to retrieve a format from
     */
    private function getFormat($requirements): ?string
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

    /**
     * @param mixed $requirements Value to retrieve an enum from
     *
     * @return mixed[]|null
     */
    private function getEnum($requirements): ?array
    {
        if ($requirements instanceof Choice) {
            if (null != $requirements->callback) {
                if (!\is_callable($choices = $requirements->callback)) {
                    return null;
                }

                return $choices();
            }

            return $requirements->choices;
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
            $weakContext = Util::createWeakContext($requestBody->_context);
            $requestBody->content[$contentType] = new OA\MediaType(
                [
                    'mediaType' => $contentType,
                    '_context' => $weakContext,
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

    private function describeCommonSchemaFromAnnotation(OA\Schema $schema, AbstractScalarParam $annotation): void
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

        $enum = $this->getEnum($annotation->requirements);
        if (null !== $enum) {
            if ($annotation->requirements instanceof Choice) {
                if ($annotation->requirements->multiple) {
                    $schema->type = 'array';
                    $schema->items = Util::createChild($schema, OA\Items::class, ['type' => 'string', 'enum' => $enum]);
                } else {
                    $schema->enum = $enum;
                }
            }
        }
    }

    /**
     * @template T of object
     *
     * @param class-string<T> $className
     *
     * @return T[]
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
