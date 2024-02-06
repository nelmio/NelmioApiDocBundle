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

use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class JMSDualComplex80
{
    /**
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @OA\Property(ref=@Model(type=JMSComplex80::class))
     */
    private $complex;

    /**
     * @OA\Property(ref=@Model(type=JMSUser::class))
     */
    private $user;
}
