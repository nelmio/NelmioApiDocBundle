<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use Symfony\Component\TypeInfo\Type;

/**
 * A type describer that stops the chain once it has described a type.
 *
 * @template T of Type
 *
 * @extends TypeDescriberInterface<T>
 */
interface StoppableTypeDescriberInterface extends TypeDescriberInterface
{
}
