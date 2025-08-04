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

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

/**
 * @template K of array-key
 * @template V
 */
class Collection
{
    /** @var array<K, V> */
    public array $map;

    /** @var array<V> */
    public array $array;

    /** @var list<V> */
    public array $list;
}

/**
 * @template T
 */
class GenericClass
{
    /** @var T */
    public mixed $genericProperty;
}

class RegularClass
{
    public string $stringProperty;
    public int $integerProperty;
}

class GenericTypes
{
    /** @var GenericClass<string> */
    public GenericClass $string; // GenericClass
    /** @var GenericClass<string> */
    public GenericClass $string2; // GenericClass
    /** @var GenericClass<int> */
    public GenericClass $integer; // GenericClass2

    /** @var GenericClass<GenericClass<int>> */
    public GenericClass $genericClass; // GenericClass3
    /** @var GenericClass<RegularClass> */
    public GenericClass $regularClass; // GenericClass4

    /** @var GenericClass<list<string>> */
    public GenericClass $stringList; // GenericClass5
    /** @var GenericClass<list<int>> */
    public GenericClass $integerList; // GenericClass6

    /** @var Collection<string, string> */
    public Collection $stringStringCollection; // Collection
    /** @var Collection<string, string> */
    public Collection $stringStringCollection2; // Collection
    /** @var Collection<int, int> */
    public Collection $integerIntegerCollection; // Collection2
    /** @var Collection<int, RegularClass> */
    public Collection $integerRegularClassCollection; // Collection3
}
