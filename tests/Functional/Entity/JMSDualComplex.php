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
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

class JMSDualComplex
{
    #[Serializer\Type('integer')]
    private $id;

    #[OA\Property(ref: new Model(type: JMSComplex::class))]
    private $complex;

    #[OA\Property(ref: new Model(type: JMSUser::class))]
    private $user;
}
