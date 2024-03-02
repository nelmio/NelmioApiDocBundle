<?php

declare(strict_types=1);

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
