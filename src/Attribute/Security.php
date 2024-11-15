<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Attribute;

use OpenApi\Annotations\AbstractAnnotation;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Security extends AbstractAnnotation
{
    public static $_types = [
        'name' => 'string',
        'scopes' => '[string]',
    ];

    public static $_required = ['name'];

    /**
     * @param array<string, mixed> $properties
     * @param string[]             $scopes
     */
    public function __construct(
        array $properties = [],
        public ?string $name = null,
        public array $scopes = [],
    ) {
        parent::__construct($properties + [
            'name' => $name,
            'scopes' => $scopes,
        ]);
    }
}
