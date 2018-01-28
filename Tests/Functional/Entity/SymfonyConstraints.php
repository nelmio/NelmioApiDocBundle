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

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Serializer\ExclusionPolicy("all")
 */
class SymfonyConstraints
{
    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyNotBlank")
     *
     * @Assert\NotBlank()
     */
    private $propertyNotBlank;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyNotNull")
     *
     * @Assert\NotNull()
     */
    private $propertyNotNull;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyAssertLengthRequired")
     *
     * @Assert\Length(min="1")
     */
    private $propertyAssertLengthRequired;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyAssertLengthMinAndMax")
     *
     * @Assert\Length(min="0", max="50")
     */
    private $propertyAssertLengthMinAndMax;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyRegex")
     *
     * @Assert\Regex(pattern="/[a-z]{2}/")
     */
    private $propertyRegex;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyCount")
     *
     * @Assert\Count(min="0", max="10")
     */
    private $propertyCount;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyChoice")
     *
     * @Assert\Choice(choices={"choice1", "choice2"})
     */
    private $propertyChoice;

    /**
     * @Serializer\Type("integer")
     * @Serializer\Expose
     * @Serializer\SerializedName("propertyExpression")
     *
     * @Assert\Expression(
     *     "this.getCategory() in ['php', 'symfony'] or !this.isTechnicalPost()",
     *     message="If this is a tech post, the category should be either php or symfony!"
     * )
     */
    private $propertyExpression;

}
