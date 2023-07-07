<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionAttribute;
use ReflectionMethod;
use ReflectionParameter;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Route;
use function in_array;
use function is_array;
use const FILTER_VALIDATE_REGEXP;
use const PHP_VERSION_ID;

final class SymfonyDescriber implements RouteDescriberInterface
{
    use RouteDescriberTrait;

    public function describe(OA\OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        if (PHP_VERSION_ID < 80100) {
            return;
        }

        $parameters = $this->getMethodParameter($reflectionMethod, [MapRequestPayload::class, MapQueryParameter::class]);

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($parameters as $parameter) {
                $parameterName = $parameter->getName();

                if ($attribute = $this->getAttribute($parameter, MapRequestPayload::class)) {
                    /** @var OA\RequestBody $requestBody */
                    $requestBody = Util::getChild($operation, OA\RequestBody::class);

                    if (!is_array($attribute->acceptFormat)) {
                        $contentSchema = $this->getContentSchemaForType($requestBody, $attribute->acceptFormat ?? 'json');
                        $contentSchema->ref = new Model(type: $parameter->getType()->getName());

                        $schema = Util::getProperty($contentSchema, $parameterName);

                        $this->describeCommonSchemaFromParameter($schema, $parameter);
                    } else {
                        foreach ($attribute->acceptFormat as $format) {
                            $contentSchema = $this->getContentSchemaForType($requestBody, $format);
                            $contentSchema->ref = new Model(type: $parameter->getType()->getName());

                            $schema = Util::getProperty($contentSchema, $parameterName);

                            $this->describeCommonSchemaFromParameter($schema, $parameter);
                        }
                    }
                }

                if ($attribute = $this->getAttribute($parameter, MapQueryParameter::class)) {
                    $operationParameter = Util::getOperationParameter($operation, $parameterName, 'query');
                    $operationParameter->name = $attribute->name ?? $parameterName;
                    $operationParameter->allowEmptyValue = $parameter->allowsNull();

                    $operationParameter->required = !$parameter->isDefaultValueAvailable() && !$parameter->allowsNull();

                    /** @var OA\Schema $schema */
                    $schema = Util::getChild($operationParameter, OA\Schema::class);

                    if (FILTER_VALIDATE_REGEXP === $attribute->filter) {
                        $schema->pattern = $attribute->options['regexp'];
                    }

                    $this->describeCommonSchemaFromParameter($schema, $parameter);
                }
            }
        }
    }

    /**
     * @param class-string[] $attributes
     *
     * @return ReflectionParameter[]
     */
    private function getMethodParameter(ReflectionMethod $reflectionMethod, array $attributes): array
    {
        $parameters = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            foreach ($parameter->getAttributes() as $attribute) {
                if (in_array($attribute->getName(), $attributes, true)) {
                    $parameters[] = $parameter;
                }
            }
        }

        return $parameters;
    }

    private function describeCommonSchemaFromParameter(OA\Schema $schema, ReflectionParameter $parameter): void
    {
        if ($parameter->isDefaultValueAvailable()) {
            $schema->default = $parameter->getDefaultValue();
        }

        if (Generator::UNDEFINED === $schema->type) {
            if ($parameter->getType()->isBuiltin()) {
                $schema->type = $parameter->getType()->getName();
            }
        }
    }

    /**
     * @param class-string<T> $attribute
     *
     * @return T|null
     *
     * @template T of object
     */
    private function getAttribute(ReflectionParameter $parameter, string $attribute): ?object
    {
        if ($attribute = $parameter->getAttributes($attribute, ReflectionAttribute::IS_INSTANCEOF)) {
            return $attribute[0]->newInstance();
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
                throw new InvalidArgumentException('Unsupported media type');
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
}
