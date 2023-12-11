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
use OpenApi\Attributes\Items;
use OpenApi\Attributes\Property;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

if (Kernel::MAJOR_VERSION < 7) {
    class SymfonyConstraintsWithValidationGroups
    {
        /**
         * @var int
         *
         * @Groups("test")
         *
         * @Assert\NotBlank(groups={"test"})
         *
         * @Assert\Range(min=1, max=100)
         */
        public $property;

        /**
         * @var int
         *
         * @Assert\Range(min=1, max=100)
         */
        public $propertyInDefaultGroup;

        /**
         * @var array
         *
         * @OA\Property(type="array", @OA\Items(type="string"))
         *
         * @Assert\Valid
         */
        public $propertyArray;
    }
} else {
    class SymfonyConstraintsWithValidationGroups
    {
        /**
         * @var int
         */
        #[Assert\Range(min: 1, max: 100)]
        #[Assert\NotBlank(groups: ['test'])]
        #[Groups('test')]
        public $property;

        /**
         * @var int
         */
        #[Assert\Range(min: 1, max: 100)]
        public $propertyInDefaultGroup;

        /**
         * @var array
         */
        #[Property(type: 'array', items: new Items(type: 'string'))]
        #[Assert\Valid]
        public $propertyArray;
    }
}
