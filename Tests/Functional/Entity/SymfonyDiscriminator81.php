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

use Symfony\Component\Serializer\Annotation\DiscriminatorMap;

#[DiscriminatorMap(
    typeProperty: 'type',
    mapping: ['one' => SymfonyDiscriminatorOne::class, 'two' => SymfonyDiscriminatorTwo::class]
)]
abstract class SymfonyDiscriminator81 extends SymfonyDiscriminator80
{
}
