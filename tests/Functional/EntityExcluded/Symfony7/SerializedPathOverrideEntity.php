<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded\Symfony7;

use Symfony\Component\Serializer\Attribute\SerializedPath;

/**
 * Tests multiple accessors with different #[SerializedPath]:
 * - Different-named getters (getDataForApi, getDataForJira) become separate properties
 * - Same-named accessors (property + getData + setData) merge: setter path wins
 */
class SerializedPathOverrideEntity
{
    #[SerializedPath('[property][value]')]
    public string $data;

    #[SerializedPath('[api][data]')]
    public function getDataForApi(): string
    {
        return $this->data;
    }

    #[SerializedPath('[jira][data]')]
    public function getDataForJira(): string
    {
        return $this->data;
    }

    #[SerializedPath('[getter][data]')]
    public function getData(): string
    {
        return $this->data;
    }

    #[SerializedPath('[setter][value]')]
    public function setData(string $data): void
    {
    }

    public string $email;
}
