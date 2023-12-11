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

use OpenApi\Annotations as OA;
use OpenApi\Attributes as OAT;
use Symfony\Component\HttpKernel\Kernel;

if (Kernel::MAJOR_VERSION < 7) {
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
    #[OAT\Schema(ref: '#/components/schemas/Test')]
    class EntityWithRef
    {
        /**
         * @var string
         */
        public $ignored = 'this property should be ignored because of the annotation above';
    }
}
