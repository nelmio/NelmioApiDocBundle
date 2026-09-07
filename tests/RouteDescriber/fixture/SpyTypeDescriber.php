<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\fixture;

use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\ObjectType;

/**
 * @implements TypeDescriberInterface<Type>
 */
final class SpyTypeDescriber implements TypeDescriberInterface
{
    /**
     * @var list<array{type: Type, context: array<string, mixed>}>
     */
    public array $calls = [];

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        $this->calls[] = ['type' => $type, 'context' => $context];

        if ($type instanceof ObjectType) {
            $schema->ref = '#/components/schemas/Stub';

            return;
        }

        $schema->type = 'string';
    }

    public function supports(Type $type, array $context = []): bool
    {
        return true;
    }
}
