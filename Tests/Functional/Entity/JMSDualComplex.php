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
use Swagger\Annotations as SWG;

class JMSDualComplex
{
    /**
     * @Serializer\Type("integer")
     */
    private $id;

    /**
     * @SWG\Property(ref=@Model(type=JMSComplex::class))
     */
    private $complex;

    /**
     * @SWG\Property(ref=@Model(type=JMSUser::class))
     */
    private $user;
}
