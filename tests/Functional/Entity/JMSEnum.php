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

class JMSEnum
{
    #[Serializer\Type('enum<'.ArticleType81::class.", 'value'>")]
    #[Serializer\Expose]
    public $enumValue;

    #[Serializer\Type('array<enum<'.ArticleType81::class.", 'value'>>")]
    #[Serializer\Expose]
    public $enumValues;

    #[Serializer\Type('enum<'.ArticleType81::class.", 'name'>")]
    #[Serializer\Expose]
    public $enumName;

    #[Serializer\Type('array<enum<'.ArticleType81::class.", 'name'>>")]
    #[Serializer\Expose]
    public $enumNames;
}
