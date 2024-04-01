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

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItems;

class Dictionary
{
    /**
     * @var array<string, string>
     */
    public $options;

    /**
     * @var array<string, string|int>
     */
    public $compoundOptions;

    /**
     * @var array<array<string, string|int>>
     */
    public $nestedCompoundOptions;

    /**
     * @var array<string, Foo>
     */
    public $modelOptions;

    /**
     * @var array<int, string>
     */
    public $listOptions;

    /**
     * @var array<int, string>|array<string, string>
     */
    public $arrayOrDictOptions;

    /**
     * @var array<string, int>
     */
    public $integerOptions;
}
