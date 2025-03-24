<?php

declare(strict_types=1);

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
use Nelmio\ApiDocBundle\Attribute\Ignore;

/**
 * JMSIgnoredProperty.
 */
#[Serializer\ExclusionPolicy('all')]
class JMSIgnoredProperty
{
    #[Serializer\Type('string')]
    #[Serializer\Expose]
    private $regularProperty;

    #[Ignore]
    #[Serializer\Type('string')]
    #[Serializer\Expose]
    private $ignoredProperty;
}
