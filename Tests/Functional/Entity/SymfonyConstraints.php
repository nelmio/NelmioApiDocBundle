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

class SymfonyConstraints
{
    /**
     * @var int
     *
     * @Assert\NotBlank()
     */
    private $propertyNotBlank;

    /**
     * @var int
     *
     * @Assert\NotNull()
     */
    private $propertyNotNull;

    /**
     * @var int
     *
     * @Assert\Length(min="1")
     */
    private $propertyAssertLengthRequired;

    /**
     * @var int
     *
     * @Assert\Length(min="0", max="50")
     */
    private $propertyAssertLengthMinAndMax;

    /**
     * @var int
     *
     * @Assert\Regex(pattern="/[a-z]{2}/")
     */
    private $propertyRegex;

    /**
     * @var int
     *
     * @Assert\Count(min="0", max="10")
     */
    private $propertyCount;

    /**
     * @var int
     *
     * @Assert\Choice(choices={"choice1", "choice2"})
     */
    private $propertyChoice;

    /**
     * @var int
     *
     * @Assert\Expression(
     *     "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()",
     *     message="If this is a tech post, the category should be either php or symfony!"
     * )
     */
    private $propertyExpression;

    /**
     * @param int $propertyNotBlank
     */
    public function setPropertyNotBlank(int $propertyNotBlank): void
    {
        $this->propertyNotBlank = $propertyNotBlank;
    }

    /**
     * @param int $propertyNotNull
     */
    public function setPropertyNotNull(int $propertyNotNull): void
    {
        $this->propertyNotNull = $propertyNotNull;
    }

    /**
     * @param int $propertyAssertLengthRequired
     */
    public function setPropertyAssertLengthRequired(int $propertyAssertLengthRequired): void
    {
        $this->propertyAssertLengthRequired = $propertyAssertLengthRequired;
    }

    /**
     * @param int $propertyAssertLengthMinAndMax
     */
    public function setPropertyAssertLengthMinAndMax(int $propertyAssertLengthMinAndMax): void
    {
        $this->propertyAssertLengthMinAndMax = $propertyAssertLengthMinAndMax;
    }

    /**
     * @param int $propertyRegex
     */
    public function setPropertyRegex(int $propertyRegex): void
    {
        $this->propertyRegex = $propertyRegex;
    }

    /**
     * @param int $propertyCount
     */
    public function setPropertyCount(int $propertyCount): void
    {
        $this->propertyCount = $propertyCount;
    }

    /**
     * @param int $propertyChoice
     */
    public function setPropertyChoice(int $propertyChoice): void
    {
        $this->propertyChoice = $propertyChoice;
    }

    /**
     * @param int $propertyExpression
     */
    public function setPropertyExpression(int $propertyExpression): void
    {
        $this->propertyExpression = $propertyExpression;
    }
}
