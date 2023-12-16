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
use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use OpenApi\Annotations as OA;

if (TestKernel::isAnnotationsAvailable()) {
    /**
     * @Serializer\ExclusionPolicy("all")
     *
     * @OA\Schema(
     *     required={"id", "user"},
     *
     *     @OA\Property(property="virtual", ref=@Model(type=JMSUser::class))
     * )
     */
    class JMSComplex extends JMSComplex80
    {
    }
} else {
    #[Serializer\ExclusionPolicy("all")]
    #[\OpenApi\Attributes\Schema(
        required: ["id", "user"],
        properties: [
            new \OpenApi\Attributes\Property(
                property: "virtual",
                ref: new Model(type: JMSUser::class)
            ),
        ],
    )]
    class JMSComplex extends JMSComplex81
    {
    }
}
