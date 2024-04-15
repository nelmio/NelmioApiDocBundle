<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\Model\ModelRegistry;

interface ModelRegistryAwareInterface
{
    /**
     * @return void
     */
    public function setModelRegistry(ModelRegistry $modelRegistry);
}
