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

    /** @var string[] */
    private array $mediaTypes;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(array $mediaTypes)
    {
        $this->mediaTypes = $mediaTypes;
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $attributes = $this->getAttributes($reflectionMethod, RequestParam::class);
        $attributes = array_merge($attributes, $this->getAttributes($reflectionMethod, QueryParam::class));

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($attributes as $attribute) {
                $parameterName = $attribute->key ?? $attribute->getName(); // the key used by fosrest

                if ($attribute instanceof QueryParam) {
                    $name = $parameterName.($attribute->map ? '[]' : '');
                    $parameter = Util::getOperationParameter($operation, $name, 'query');
                    $parameter->allowEmptyValue = $attribute->nullable && $attribute->allowBlank;

                    $parameter->required = !$attribute->nullable && $attribute->strict;

                    if (Generator::UNDEFINED === $parameter->description) {
                        $parameter->description = $attribute->description;
                    }

                    if ($attribute->map) {
                        $parameter->explode = true;
                    }

                    $schema = Util::getChild($parameter, OA\Schema::class);
                    $this->describeCommonSchemaFromAttribute($schema, $attribute, $reflectionMethod);
                } else {
                    /** @var OA\RequestBody $requestBody */
                    $requestBody = Util::getChild($operation, OA\RequestBody::class);
                    foreach ($this->mediaTypes as $mediaType) {
                        $contentSchema = $this->getContentSchemaForType($requestBody, $mediaType);
                        $schema = Util::getProperty($contentSchema, $parameterName);

                        if (!$attribute->nullable && $attribute->strict) {
                            $requiredParameters = \is_array($contentSchema->required) ? $contentSchema->required : [];
                            $requiredParameters[] = $parameterName;

                            $contentSchema->required = array_values(array_unique($requiredParameters));
                        }
                        $this->describeCommonSchemaFromAttribute($schema, $attribute, $reflectionMethod);
                    }
                }
            }
        }
    }

    /**
     * @param mixed $requirements Value to retrieve a pattern from
     */
    private function getPattern(mixed $requirements): ?string
    {
        if (\is_array($requirements) && isset($requirements['rule'])) {
            return (string) $requirements['rule'];
        }

        if (\is_string($requirements)) {
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
    private function getFormat(mixed $requirements): ?string
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
    private function getEnum(mixed $requirements, \ReflectionMethod $reflectionMethod): ?array
    {
        if (!($requirements instanceof Choice)) {
            return null;
        }

        if (null === $requirements->callback) {
            return $requirements->choices;
        }

        if (\is_callable($choices = $requirements->callback)
            || \is_callable($choices = [$reflectionMethod->class, $requirements->callback])
        ) {
            return $choices();
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

    private function describeCommonSchemaFromAttribute(OA\Schema $schema, AbstractScalarParam $attribute, \ReflectionMethod $reflectionMethod): void
    {
        $schema->default = $attribute->getDefault();

        if (Generator::UNDEFINED === $schema->type) {
            $schema->type = $attribute->map ? 'array' : 'string';
        }

        if ($attribute->map) {
            $schema->type = 'array';
            $schema->items = Util::getChild($schema, OA\Items::class);
        }

        $pattern = $this->getPattern($attribute->requirements);
        if (null !== $pattern) {
            $schema->pattern = $pattern;
        }

        $format = $this->getFormat($attribute->requirements);
        if (null !== $format) {
            $schema->format = $format;
        }

        $enum = $this->getEnum($attribute->requirements, $reflectionMethod);
        if (null !== $enum) {
            if ($attribute->requirements instanceof Choice) {
                if ($attribute->requirements->multiple) {
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
    private function getAttributes(\ReflectionMethod $reflection, string $className): array
    {
        $attributes = [];
        foreach ($reflection->getAttributes($className, \ReflectionAttribute::IS_INSTANCEOF) as $attribute) {
            $attributes[] = $attribute->newInstance();
        }

        return $attributes;
    }
}
