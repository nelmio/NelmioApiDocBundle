<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SchemaDescriber;

/**
 * @experimental
 */
interface SchemaDescriberAwareInterface
{
    public function setDescriber(SchemaDescriberInterface $describer): void;
}
