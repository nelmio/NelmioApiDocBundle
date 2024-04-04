<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\QueryModel;

use OpenApi\Attributes as OA;

class ArrayQueryModel
{
    public array $ids;
    #[OA\Property(description: 'List of product ids', type: 'array', items: new OA\Items(type: 'integer'))]
    private ?array $productIds = null;

    public function getProductIds(): ?array
    {
        return $this->productIds;
    }
}
