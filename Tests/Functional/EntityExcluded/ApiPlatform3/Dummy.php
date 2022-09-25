<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\ApiPlatform3;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Guilhem N. <egetick@gmail.com>
 */
#[
    ApiResource(
        operations: [
            new Get(name: 'get'),
            new Get(name: 'custom2', uriTemplate: '/foo'),
            new Post(name: 'custom', uriTemplate: '/foo'),
            new GetCollection(),
        ],
    )
]
class Dummy
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     */
    #[ApiProperty(iris: ['http://schema.org/name'])]
    private $name;

    public function getId(): int
    {
        return $this->id;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
