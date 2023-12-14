<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use Symfony\Component\Routing\Annotation\Route;

if (PHP_VERSION_ID < 80100) {
    /**
     * @Route("/api", host="api.example.com")
     */
    class NameConverterController extends NameConverterController80
    {
    }
} else {
    #[Route('/api', host: 'api.example.com')]
    class NameConverterController extends NameConverterController81
    {
    }
}
