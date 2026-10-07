<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\TypeInfo;

use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityWithAlternateType;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;

class ArrayWithoutKeyType
{
    /**
     * @var ?array<string>
     */
    public ?array $nullableArray;

    /**
     * @var array<string>|null
     */
    public ?array $arrayOrNull;

    /**
     * @var array<?string>
     */
    public array $nullableValues;

    /**
     * @var array<string|int, bool>
     */
    public array $stringOrIntKeys;

    /**
     * @var array<array-key, float>
     */
    public array $arrayKeyPseudoType;

    /**
     * @var iterable<string>
     */
    public iterable $iterable;

    /**
     * @var array<array<int>>
     */
    public array $nested;

    /**
     * @var array<string, array<string>>
     */
    public array $dictOfLists;

    /**
     * @var array<User>
     */
    public array $objects;

    /**
     * @var \Traversable<User>
     */
    public \Traversable $traversable;

    /**
     * @var list<EntityWithAlternateType>
     */
    public array $listOfTraversables;

    /**
     * @var array<EntityWithAlternateType>
     */
    public array $arrayOfTraversables;
}
