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
use Symfony\Component\TypeInfo\Type\TemplateType;

/**
 * @implements TypeDescriberInterface<TemplateType>
 *
 * @internal
 */
final class TemplateDescriber implements TypeDescriberInterface, TypeDescriberAwareInterface
{
    use TypeDescriberAwareTrait;

    public const TEMPLATES_KEY = '_nelmio_template_types';

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $templateTypes = $context[self::TEMPLATES_KEY];
        unset($context[self::TEMPLATES_KEY]);

        if (\array_key_exists($type->getName(), $templateTypes)) {
            $resolvedType = $templateTypes[$type->getName()];

            $this->describer->describe($resolvedType, $schema, $context);
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        return $type instanceof TemplateType
            && \array_key_exists(self::TEMPLATES_KEY, $context);
    }
}
