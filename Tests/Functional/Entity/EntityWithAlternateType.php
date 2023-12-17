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
     * @OA\Schema(type="array", @OA\Items(type="string"))
     */
    class EntityWithAlternateType implements \IteratorAggregate
    {
        /**
         * @var string
         */
        public $ignored = 'this property should be ignored because of the annotation above';

        public function getIterator(): \ArrayIterator
        {
            return new \ArrayIterator([
                'abc',
                'def',
            ]);
        }
    }
} else {
    #[\OpenApi\Attributes\Schema(type: 'array', items: new \OpenApi\Attributes\Items(type: 'string'))]
    class EntityWithAlternateType implements \IteratorAggregate
    {
        /**
         * @var string
         */
        public $ignored = 'this property should be ignored because of the annotation above';

        public function getIterator(): \ArrayIterator
        {
            return new \ArrayIterator([
                'abc',
                'def',
            ]);
        }
    }
}
