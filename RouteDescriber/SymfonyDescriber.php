<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber;

use InvalidArgumentException;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Route;
use function in_array;
use function is_array;
use const FILTER_VALIDATE_REGEXP;
use const PHP_VERSION_ID;

final class SymfonyDescriber implements RouteDescriberInterface
{
    private const PHP_VERSION_ERROR = self::class.' can only be used in PHP 8 or above.';

    use RouteDescriberTrait;

    public function describe(OA\OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        if (PHP_VERSION_ID < 80100) {
            throw new RuntimeException(self::PHP_VERSION_ERROR);
        }

        $parameters = $this->getMethodParameter($reflectionMethod, [MapRequestPayload::class, MapQueryParameter::class]);

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($parameters as $parameter) {
                $parameterName = $parameter->getName();

                if ($attribute = $this->getAttribute($parameter, MapRequestPayload::class)) {
                    /** @var OA\RequestBody $requestBody */
                    $requestBody = Util::getChild($operation, OA\RequestBody::class);

                    if (!is_array($attribute->acceptFormat)) {
                        $this->describeRequestBody($requestBody, $parameter, $attribute->acceptFormat ?? 'json');
                    } else {
                        foreach ($attribute->acceptFormat as $format) {
                            $this->describeRequestBody($requestBody, $parameter, $format);
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
        if (PHP_VERSION_ID < 80100) {
            throw new RuntimeException(self::PHP_VERSION_ERROR);
        }

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
        if (PHP_VERSION_ID < 80100) {
            throw new RuntimeException(self::PHP_VERSION_ERROR);
        }

        if ($attribute = $parameter->getAttributes($attribute, \ReflectionAttribute::IS_INSTANCEOF)) {
            return $attribute[0]->newInstance();
        }

        return null;
    }

    private function describeRequestBody(OA\RequestBody $requestBody, ReflectionParameter $parameter, string $format): void
    {
        $contentSchema = $this->getContentSchemaForType($requestBody, $format);
        $contentSchema->ref = new Model(type: $parameter->getType()->getName());
        $contentSchema->type = 'object';

        $schema = Util::getProperty($contentSchema, $parameter->getName());

        $this->describeCommonSchemaFromParameter($schema, $parameter);
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
