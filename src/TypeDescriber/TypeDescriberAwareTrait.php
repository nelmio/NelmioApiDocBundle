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

trait TypeDescriberAwareTrait
{
    protected TypeDescriberInterface $describer;

    public function setDescriber(TypeDescriberInterface $describer): void
    {
        $this->describer = $describer;
    }
}
