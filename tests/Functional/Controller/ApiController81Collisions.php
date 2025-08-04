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

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article81WithGroups;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

class ApiController81Collisions
{
    #[Route('/article_81_group_full', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Article81WithGroups::class, groups: ['full']))]
    public function article81GroupFull()
    {
    }

    #[Route('/article_81_group_default', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Article81WithGroups::class, groups: ['default']))]
    public function article81GroupDefault()
    {
    }

    #[Route('/article_81_group_default_and_full', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Article81WithGroups::class, groups: ['default', 'full']))]
    public function article81GroupDefaultAndFull()
    {
    }

    #[Route('/article_81_group_empty', methods: ['GET'])]
    #[OA\Response(response: 200, description: 'Success', content: new Model(type: Article81WithGroups::class, groups: []))]
    public function article81GroupEmpty()
    {
    }
}
