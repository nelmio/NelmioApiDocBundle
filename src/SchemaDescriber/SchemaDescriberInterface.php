<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SchemaDescriber;

use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;

/**
 * @template T of Type
 *
 * @experimental
 */
interface SchemaDescriberInterface
{
    /**
     * @param T                    $type
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(Type $type, Schema $schema, array $context = []): void;

    /**
     * @param T                    $type
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function supports(Type $type, array $context = []): bool;
}
