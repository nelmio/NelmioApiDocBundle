<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItems;

class Dictionary
{
    /**
     * @var array<string, string>
     */
    public $options;

    /**
     * @var array<string, string|integer>
     */
    public $compoundOptions;

    /**
     * @var array<array<string, string|integer>>
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
     * @var array<string, integer>
     */
    public $integerOptions;
}
