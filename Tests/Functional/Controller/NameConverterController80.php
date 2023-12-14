<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityThroughNameConverter;
use OpenApi\Annotations as OA;
use Symfony\Component\Routing\Annotation\Route;

class NameConverterController80
{
    /**
     * @Route("/name_converter_context", methods={"GET"})
     *
     * @OA\Response(
     *    response="200",
     *    description="",
     *
     *    @Model(type=EntityThroughNameConverter::class, serializationContext={"secret_name_converter_value"=true})
     * )
     */
    public function nameConverterContext()
    {
    }
}
