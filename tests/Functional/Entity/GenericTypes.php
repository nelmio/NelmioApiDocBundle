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
 * @template V
 */
class StringCollection
{
    /** @var array<string, V> */
    public array $map;

    /** @var array<V> */
    public array $array;

    /** @var list<V> */
    public array $list;
}

/**
 * @template V
 */
class IntCollection
{
    /** @var array<int, V> */
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

    /** @var StringCollection<string> */
    public StringCollection $stringStringCollection; // Collection
    /** @var StringCollection<string> */
    public StringCollection $stringStringCollection2; // Collection
    /** @var IntCollection<int> */
    public IntCollection $integerIntegerCollection; // Collection2
    /** @var IntCollection<RegularClass> */
    public IntCollection $integerRegularClassCollection; // Collection3
}
