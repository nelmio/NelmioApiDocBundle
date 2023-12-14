<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\EntityThroughNameConverter;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

if (PHP_VERSION_ID < 80100) {
    /**
     * @Route("/api", host="api.example.com")
     */
    class NameConverterController
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
} else {
    #[Route('/api', host: 'api.example.com')]
    class NameConverterController
    {
        #[Route('/name_converter_context', methods: ['GET'])]
        #[OA\Response(response: '200', description: '', content: new Model(type: EntityThroughNameConverter::class, serializationContext: ['secret_name_converter_value' => true]))]
        public function nameConverterContext()
        {
        }
    }
}
