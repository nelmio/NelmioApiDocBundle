<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;

final class SecurityDescriber implements DescriberInterface
{
    /**
     * @var array<string, array<string, mixed>>
     */
    private array $securitySchemes;

    /**
     * @param array<string, array<string, mixed>> $securitySchemes
     */
    public function __construct(array $securitySchemes)
    {
        $this->securitySchemes = $securitySchemes;
    }

    public function describe(OA\OpenApi $api): void
    {
        foreach ($this->securitySchemes as $name => $securityScheme) {
            Util::getCollectionItem(
                $api->components,
                OA\SecurityScheme::class,
                $securityScheme + ['securityScheme' => $name],
            );
        }
    }
}
