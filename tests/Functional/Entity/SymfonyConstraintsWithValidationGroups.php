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

use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

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
    #[OA\Property(type: 'array', items: new OA\Items(type: 'string'))]
    #[Assert\Valid]
    public $propertyArray;

    /**
     * @var ?string
     */
    #[Groups(['test'])]
    #[Assert\NotNull(groups: ['test'])]
    public $propertyNotNullOnSpecificGroup;
}
