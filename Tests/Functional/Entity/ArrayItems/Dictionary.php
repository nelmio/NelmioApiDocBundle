<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItems;

// PHP 7.2 is not able to guess these types
if (PHP_VERSION_ID < 70300) {
    class Dictionary
    {
    }
} else {
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
}
