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

        string $nonNullableNonPromotedPropertyWithDefault = 'nonNullableNonPromotedPropertyWithDefault',
        ?int $nullableNonPromotedPropertyWithDefault = null,

        public int $nonNullablePromotedPropertyWithDefault = 4711,
        public ?string $nullablePromotedPropertyWithDefault = null,
    ) {
        $this->nonNullableNonPromotedPropertyWithDefault = $nonNullableNonPromotedPropertyWithDefault;
    }
}
