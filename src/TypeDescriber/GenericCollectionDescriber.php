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

use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\TemplateType;

/**
 * @implements TypeDescriberInterface<CollectionType>
 *
 * @internal
 */
final class GenericCollectionDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        if (!$type->getCollectionKeyType() instanceof TemplateType) {
            throw new \LogicException('This describer only supports '.CollectionType::class.' with '.TemplateType::class.' as key type.');
        }

        $templateTypes = $context[TemplateDescriber::TEMPLATES_KEY];
        unset($context[TemplateDescriber::TEMPLATES_KEY]);

        if (\array_key_exists($type->getCollectionKeyType()->getName(), $templateTypes)) {
            $resolvedKeyType = $templateTypes[$type->getCollectionKeyType()->getName()];
            $valueTemplateName = $type->getCollectionValueType() instanceof TemplateType
                ? $type->getCollectionValueType()->getName()
                : null;
            $resolvedValueType = $templateTypes[$valueTemplateName] ?? $type->getCollectionValueType();

            $collectionType = Type::array($resolvedValueType, $resolvedKeyType, $type->isList());

            $this->describer->describe($collectionType, $schema, $context);
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof CollectionType
            && $type->getCollectionKeyType() instanceof TemplateType
            && \array_key_exists(TemplateDescriber::TEMPLATES_KEY, $context);
    }
}
