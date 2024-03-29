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

use Symfony\Component\Validator\Constraints as Assert;

class EntityWithPromotedPropertiesWithDefaults80
{
    /**
     * @Assert\NotBlank()
     */
    public string $order;

    public function __construct(
        string $order = 'asc',

        public int $page = 30,
        public string $sort = 'id',
    ) {
        $this->order = $order;
    }
}
