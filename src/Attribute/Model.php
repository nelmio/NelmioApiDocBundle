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

use OpenApi\Annotations\Parameter;
use OpenApi\Attributes\Attachable;
use OpenApi\Generator;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
class Model extends Attachable
{
    public static $_types = [
        'type' => 'string',
        'groups' => '[string]',
        'options' => '[mixed]',
    ];

    public static $_required = ['type'];

    public static $_parents = [
        Parameter::class,
    ];

    public string $type;

    /**
     * @var string[]|null
     */
    public ?array $groups;

    /**
     * @var mixed[]
     */
    public array $options;

    /**
     * @var array<string, mixed>
     */
    public array $serializationContext;

    /**
     * @param mixed[]              $properties
     * @param string[]             $groups
     * @param mixed[]              $options
     * @param array<string, mixed> $serializationContext
     */
    public function __construct(
        array $properties = [],
        string $type = Generator::UNDEFINED,
        ?array $groups = null,
        ?array $options = [],
        array $serializationContext = []
    ) {
        if (null === $options) {
            trigger_deprecation('nelmio/api-doc-bundle', '4.33.4', 'Passing null to the "$options" argument of "%s()" is deprecated, pass an empty array instead.', __METHOD__);
            $options = [];
        }

        parent::__construct($properties + [
            'type' => $type,
            'groups' => $groups,
            'options' => $options,
            'serializationContext' => $serializationContext,
        ]);
    }
}
