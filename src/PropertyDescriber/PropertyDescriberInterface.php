<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use OpenApi\Annotations\Schema;
use Symfony\Component\PropertyInfo\Type;

interface PropertyDescriberInterface
{
    /**
     * @param Type[]               $types
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, Schema $property, array $context = []): void;

    /**
     * @param Type[]               $types
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function supports(array $types, array $context = []): bool;
}
