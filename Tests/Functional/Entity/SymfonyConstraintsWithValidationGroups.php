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

use Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture as CustomAssert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

class SymfonyConstraintsWithValidationGroups
{
    /**
     * @var int
     *
     * @Groups("test")
     * @Assert\NotBlank(groups={"test"})
     * @Assert\Range(min=1, max=100)
     */
    public $property;

    /**
     * @var int
     *
     * @Assert\Range(min=1, max=100)
     */
    public $propertyInDefaultGroup;
}
