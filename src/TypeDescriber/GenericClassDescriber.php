<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use OpenApi\Annotations\Schema;
use phpDocumentor\Reflection\DocBlock\Tags\Template;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\GenericType;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * @implements TypeDescriberInterface<GenericType>
 *
 * @internal
 */
final class GenericClassDescriber implements TypeDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private DocBlockFactoryInterface $docBlockFactory;

    public function __construct()
    {
        $this->docBlockFactory = DocBlockFactory::createInstance();
    }

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $wrappedType = $type->getWrappedType();
        $reflectionClass = new \ReflectionClass($wrappedType->getClassName());

        if (false !== $reflectionClass->getDocComment()) {
            /** @var Template[] $templateTags */
            $templateTags = $this->docBlockFactory
                ->create($reflectionClass)
                ->getTagsByName('template');
            $templateNames = array_map(
                static fn (Template $template): string => $template->getTemplateName(),
                $templateTags,
            );

            if ([] !== $templateNames) {
                $context[TemplateDescriber::TEMPLATES_KEY] = array_combine($templateNames, $type->getVariableTypes());
            }
        }

        $schema->ref = $this->modelRegistry->register(
            new Model(new LegacyType('object', false, $wrappedType->getClassName()), serializationContext: $context)
        );
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof GenericType
            && $type->getWrappedType() instanceof ObjectType;
    }
}
