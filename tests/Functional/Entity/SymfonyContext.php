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

use Symfony\Component\Serializer\Attribute\Context;

class SymfonyContext
{
    /**
     * @var \DateTime
     */
    #[Context(['datetime_format' => 'Y-m-d'])]
    public $date;

    #[Context(['datetime_format' => 'Y-m-d'])]
    public ?\DateTime $nullableDate = null;
}
