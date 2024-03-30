<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

if (PHP_VERSION_ID >= 80000) {
    // Due to backward compatibility with PHP 7.4, eval() has to be used in addition to checking the PHP_VERSION_ID,
    // as the PHP 8.0 promoted properties syntax will otherwise still cause ParseErrors when running the test suite.
    eval('
        use Symfony\Component\Validator\Constraints as Assert;

        class EntityWithPromotedPropertiesWithDefaults80
        {
            /**
             * @Assert\NotBlank()
             */
            public string $nonNullableNonPromotedPropertyWithDefault;

            public function __construct(
                int $nonNullableNonPromotedProperty,
                ?string $nullableNonPromotedProperty,

                string $nonNullableNonPromotedPropertyWithDefault = \'nonNullableNonPromotedPropertyWithDefault\',
                ?int $nullableNonPromotedPropertyWithDefault = null,

                public int $nonNullablePromotedPropertyWithDefault = 4711,
                public ?string $nullablePromotedPropertyWithDefault = null,
            ) {
                $this->nonNullableNonPromotedPropertyWithDefault = $nonNullableNonPromotedPropertyWithDefault;
            }
        }
    ');
} else {
    class EntityWithPromotedPropertiesWithDefaults80
    {
    }
}
