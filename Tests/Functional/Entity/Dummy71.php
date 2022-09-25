<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Guilhem N. <egetick@gmail.com>
 *
 * @ApiResource(
 *   shortName="Dummy",
 *   collectionOperations={
 *     "get"={"method"="GET"},
 *     "custom2"={"path"="/foo", "method"="GET"},
 *     "custom"={"path"="/foo", "method"="POST"},
 *   },
 *   itemOperations={"get"={"method"="GET"}})
 * )
 */
class Dummy71
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\NotBlank
     * @ApiProperty(iri="http://schema.org/name")
     */
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
