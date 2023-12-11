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

use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpKernel\Kernel;

if (Kernel::MAJOR_VERSION < 7) {
    class EntityWithNullableSchemaSet
    {
        /**
         * @var ?string
         *
         * @OA\Property()
         */
        public $nullablePropertyNullableNotSet;

        /**
         * @var ?string
         *
         * @OA\Property(nullable=false)
         */
        public $nullablePropertyNullableFalseSet;

        /**
         * @var ?string
         *
         * @OA\Property(nullable=true)
         */
        public $nullablePropertyNullableTrueSet;

        /**
         * @var string
         *
         * @OA\Property()
         */
        public $nonNullablePropertyNullableNotSet;

        /**
         * @var string
         *
         * @OA\Property(nullable=false)
         */
        public $nonNullablePropertyNullableFalseSet;

        /**
         * @var string
         *
         * @OA\Property(nullable=true)
         */
        public $nonNullablePropertyNullableTrueSet;
    }
} else {
    class EntityWithNullableSchemaSet
    {
        /**
         * @var ?string
         */
        #[OAT\Property]
        public $nullablePropertyNullableNotSet;

        /**
         * @var ?string
         */
        #[OAT\Property(nullable: false)]
        public $nullablePropertyNullableFalseSet;

        /**
         * @var ?string
         */
        #[OAT\Property(nullable: true)]
        public $nullablePropertyNullableTrueSet;

        /**
         * @var string
         */
        #[OAT\Property]
        public $nonNullablePropertyNullableNotSet;

        /**
         * @var string
         */
        #[OAT\Property(nullable: false)]
        public $nonNullablePropertyNullableFalseSet;

        /**
         * @var string
         */
        #[OAT\Property(nullable: true)]
        public $nonNullablePropertyNullableTrueSet;
    }
}
