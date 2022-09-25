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

use ApiPlatform\Core\Annotation\ApiProperty;

if (!class_exists(ApiProperty::class)) {
    class_alias(Dummy81::class, Dummy::class);
} else {
    class_alias(Dummy71::class, Dummy::class);
}