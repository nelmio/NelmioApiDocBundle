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

use OpenApi\Annotations as OA;
use OpenApi\Generator;

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
        $securitySchemes = Generator::UNDEFINED !== $api->components && Generator::UNDEFINED !== $api->components->securitySchemes ? $api->components->securitySchemes : [];

        foreach ($this->securitySchemes as $name => $securityScheme) {
            $securitySchemes[] = new OA\SecurityScheme([
                'securityScheme' => $name,
                ...$securitySchemes,
            ]);
        }
    }
}
