<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

use OpenApi\Annotations\AbstractAnnotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Security extends AbstractAnnotation
{
    /** {@inheritdoc} */
    public static $_types = [
        'name' => 'string',
        'scopes' => '[string]',
    ];

    public static $_required = ['name'];

    /**
     * @var string
     */
    public $name;

    /**
     * @var string[]
     */
    public $scopes = [];

    public function __construct(
        array $properties = [],
        string $name = null,
        array $scopes = []
    ) {
        parent::__construct($properties + [
            'name' => $name,
            'scopes' => $scopes,
        ]);
    }
}
