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

use Nelmio\ApiDocBundle\Attribute\Model as ModelAttribute;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Route;
use Symfony\Component\TypeInfo\Exception\UnsupportedException;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\BuiltinType;
use Symfony\Component\TypeInfo\TypeIdentifier;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolver;
use Symfony\Component\TypeInfo\TypeResolver\TypeResolverInterface;

final class SymfonySerializeDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;
    use RouteDescriberTrait;

    private TypeResolverInterface $typeResolver;

    /**
     * @param string[] $mediaTypes
     */
    public function __construct(
        private array $mediaTypes,
        private TypeDescriberInterface $typeDescriber,
        ?TypeResolverInterface $typeResolver = null,
    ) {
        $this->typeResolver = $typeResolver ?? TypeResolver::create();
    }

    public function describe(OA\OpenApi $api, Route $route, \ReflectionMethod $reflectionMethod): void
    {
        $attributes = $reflectionMethod->getAttributes(Serialize::class);
        if ([] === $attributes) {
            return;
        }

        $attribute = $attributes[0]->newInstance();
        $returnType = $this->resolveReturnType($reflectionMethod);

        // The framework serializes the controller result only when it is not already a Response,
        // so the attribute has no effect at all on such a method.
        if (null !== $returnType && $returnType->isIdentifiedBy(Response::class)) {
            return;
        }

        $type = null !== $returnType ? $this->describableType($returnType, $attribute->context) : null;

        foreach ($this->getOperations($api, $route) as $operation) {
            $response = $this->getResponse($operation, $attribute->code);

            if (Generator::UNDEFINED === $response->description) {
                $response->description = '';
            }

            $this->describeHeaders($response, $attribute);

            if (null === $type || $this->hasContent($response)) {
                continue;
            }

            foreach ($this->mediaTypes as $mediaType) {
                $schema = $this->getContentSchemaForType($response, $mediaType);

                $this->typeDescriber->describe($type, $schema, $attribute->context);
            }
        }
    }

    private function resolveReturnType(\ReflectionMethod $reflectionMethod): ?Type
    {
        if (null === $reflectionMethod->getReturnType()) {
            return null;
        }

        try {
            return $this->typeResolver->resolve($reflectionMethod);
        } catch (UnsupportedException) {
            return null;
        }
    }

    /**
     * @param array<string, mixed> $context
     */
    private function describableType(Type $type, array $context): ?Type
    {
        // Type::isIdentifiedBy(TypeIdentifier::NULL) is true for every nullable type, so the
        // identifier has to be compared on the builtin type itself.
        if ($type instanceof BuiltinType && \in_array(
            $type->getTypeIdentifier(),
            [TypeIdentifier::VOID, TypeIdentifier::NEVER, TypeIdentifier::NULL],
            true,
        )) {
            return null;
        }

        if ($this->typeDescriber instanceof ModelRegistryAwareInterface) {
            $this->typeDescriber->setModelRegistry($this->modelRegistry);
        }

        return $this->typeDescriber->supports($type, $context) ? $type : null;
    }

    private function getResponse(OA\Operation $operation, int $code): OA\Response
    {
        $responses = Generator::UNDEFINED !== $operation->responses ? $operation->responses : [];

        // OA\Response::$response is declared as int|string and swagger-php never normalizes it,
        // while Util::searchIndexedCollectionItem() compares strictly.
        foreach ($responses as $response) {
            if ($code === $response->response || (string) $code === $response->response) {
                return $response;
            }
        }

        return Util::getIndexedCollectionItem($operation, OA\Response::class, (string) $code);
    }

    private function describeHeaders(OA\Response $response, Serialize $attribute): void
    {
        foreach (array_keys($attribute->headers) as $name) {
            if ('content-type' === strtolower((string) $name)) {
                continue;
            }

            $header = Util::getIndexedCollectionItem($response, OA\Header::class, (string) $name);
            $schema = Util::getChild($header, OA\Schema::class);

            if (Generator::UNDEFINED === $schema->type) {
                $schema->type = 'string';
            }

            if (Generator::UNDEFINED === $header->required) {
                $header->required = true;
            }
        }
    }

    /**
     * Content declared by the user may still be unmerged at this point: a #[Model] sits in
     * $attachables until ModelRegister runs, and an #[OA\JsonContent] stays in $_unmerged
     * until the MergeJsonContent processor moves it into $content.
     */
    private function hasContent(OA\Response $response): bool
    {
        if (Generator::UNDEFINED !== $response->content) {
            return true;
        }

        if (Generator::UNDEFINED !== $response->attachables) {
            foreach ($response->attachables as $attachable) {
                if ($attachable instanceof ModelAttribute) {
                    return true;
                }
            }
        }

        foreach ($response->_unmerged as $unmerged) {
            if ($unmerged instanceof OA\MediaType || $unmerged instanceof OA\Schema) {
                return true;
            }
        }

        return false;
    }

    private function getContentSchemaForType(OA\Response $response, string $type): OA\Schema
    {
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

        return Util::getChild(
            Util::getIndexedCollectionItem($response, OA\MediaType::class, $contentType),
            OA\Schema::class
        );
    }
}
