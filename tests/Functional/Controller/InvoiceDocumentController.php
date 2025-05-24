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
use Symfony\Component\Routing\Attribute\Route;

#[Route('/invoices', methods: 'GET')]
#[OA\Tag(name: 'Invoices')]
final class InvoiceDocumentController extends AbstractDocumentController
{
}
