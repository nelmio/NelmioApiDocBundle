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

use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use OpenApi\Annotations as OA;

if (TestKernel::isAnnotationsAvailable()) {
    /**
     * @OA\Schema(ref="#/components/schemas/Test")
     */
    class EntityWithRef
    {
        /**
         * @var string
         */
        public $ignored = 'this property should be ignored because of the annotation above';
    }
} else {
    #[\OpenApi\Attributes\Schema(ref: '#/components/schemas/Test')]
    class EntityWithRef
    {
        /**
         * @var string
         */
        public $ignored = 'this property should be ignored because of the annotation above';
    }
}
