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
     * @Assert\Length(min="0", max="50")
     */
    private $propertyAssertLength;

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
     * @Assert\Choice(callback={SymfonyConstraints::class,"fetchAllowedChoices"})
     */
    private $propertyChoiceWithCallback;

    /**
     * @var int
     *
     * @Assert\Choice(callback="fetchAllowedChoices")
     */
    private $propertyChoiceWithCallbackWithoutClass;

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
     * @var int
     *
     * @Assert\Range(min=1, max=5)
     */
    private $propertyRange;

    /**
     * @var int
     *
     * @Assert\LessThan(42)
     */
    private $propertyLessThan;

    /**
     * @var int
     *
     * @Assert\LessThanOrEqual(23)
     */
    private $propertyLessThanOrEqual;

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
     * @param int $propertyAssertLength
     */
    public function setPropertyAssertLength(int $propertyAssertLength): void
    {
        $this->propertyAssertLength = $propertyAssertLength;
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
     * @param int $propertyChoiceWithCallback
     */
    public function setPropertyChoiceWithCallback(int $propertyChoiceWithCallback): void
    {
        $this->propertyChoiceWithCallback = $propertyChoiceWithCallback;
    }

    /**
     * @param int $propertyChoiceWithCallbackWithoutClass
     */
    public function setPropertyChoiceWithCallbackWithoutClass(int $propertyChoiceWithCallbackWithoutClass): void
    {
        $this->propertyChoiceWithCallbackWithoutClass = $propertyChoiceWithCallbackWithoutClass;
    }

    /**
     * @param int $propertyExpression
     */
    public function setPropertyExpression(int $propertyExpression): void
    {
        $this->propertyExpression = $propertyExpression;
    }

    /**
     * @param int $propertyRange
     */
    public function setPropertyRange(int $propertyRange): void
    {
        $this->propertyRange = $propertyRange;
    }

    /**
     * @param int $propertyLessThan
     */
    public function setPropertyLessThan(int $propertyLessThan): void
    {
        $this->propertyLessThan = $propertyLessThan;
    }

    /**
     * @param int $propertyLessThanOrEqual
     */
    public function setPropertyLessThanOrEqual(int $propertyLessThanOrEqual): void
    {
        $this->propertyLessThanOrEqual = $propertyLessThanOrEqual;
    }

    /**
     * @return array
     */
    public static function fetchAllowedChoices()
    {
        return ['choice1', 'choice2'];
    }
}
