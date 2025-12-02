<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Annotation\Route;

class MapUploadedFileController
{
    #[Route('/article_map_uploaded_file', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createUploadFromMapUploadedFilePayload(
        #[MapUploadedFile]
        UploadedFile $upload,
    ) {
    }

    #[Route('/article_map_uploaded_file_nullable', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createUploadFromMapUploadedFilePayloadNullable(
        #[MapUploadedFile]
        ?UploadedFile $upload,
    ) {
    }

    #[Route('/article_map_uploaded_file_multiple', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createUploadFromMapUploadedFilePayloadMultiple(
        #[MapUploadedFile]
        UploadedFile $firstUpload,
        #[MapUploadedFile]
        UploadedFile $secondUpload,
    ) {
    }

    #[Route('/article_map_uploaded_file_add_to_existing', methods: ['POST'])]
    #[OA\RequestBody(content: [
        new OA\MediaType('multipart/form-data', new OA\Schema(
            properties: [new OA\Property(property: 'existing', type: 'string', format: 'binary')],
            type: 'object',
        )),
    ])]
    #[OA\Response(response: '200', description: '')]
    public function createUploadFromMapUploadedFileAddToExisting(
        #[MapUploadedFile]
        ?UploadedFile $upload,
    ): void {
    }

    #[Route('/article_map_uploaded_file_overwrite', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'Body if file upload request',
        content: [
            new OA\MediaType('multipart/form-data', new OA\Schema(
                properties: [new OA\Property(
                    property: 'upload',
                    description: 'A file',
                )],
                type: 'object',
            )),
        ],
    )]
    #[OA\Response(response: '200', description: '')]
    public function createUploadFromMapUploadedFileOverwrite(
        #[MapUploadedFile]
        ?UploadedFile $upload,
    ): void {
    }

    #[Route('/article_map_uploaded_file_array', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createArrayUploadFromMapUploadedFilePayload(
        #[MapUploadedFile]
        array $upload,
    ) {
    }

    #[Route('/article_map_uploaded_file_variadic', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function createVariadicUploadFromMapUploadedFilePayload(
        #[MapUploadedFile]
        UploadedFile ...$upload,
    ) {
    }
}
