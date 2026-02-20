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

use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\Routing\Attribute\Route;

class MapUploadedFileWithRequestPayloadController
{
    #[Route('/article_upload_with_payload', methods: ['POST'])]
    #[OA\Response(response: '200', description: '')]
    public function uploadWithPayload(
        #[MapRequestPayload(acceptFormat: 'json')]
        Article81 $article81,
        #[MapUploadedFile]
        UploadedFile $upload,
    ): void {
    }
}
