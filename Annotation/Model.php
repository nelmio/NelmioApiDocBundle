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
use OpenApi\Annotations\Parameter;
use OpenApi\Generator;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_METHOD)]
final class Model extends AbstractAnnotation
{
    /** {@inheritdoc} */
    public static $_types = [
        'type' => 'string',
        'groups' => '[string]',
        'options' => '[mixed]',
    ];

    public static $_required = ['type'];

    public static $_parents = [
        Parameter::class,
    ];

    /**
     * @var string
     */
    public $type;

    /**
     * @var string[]
     */
    public $groups;

    /**
     * @var mixed[]
     */
    public $options;

    /**
     * @param mixed[]  $properties
     * @param string[] $groups
     * @param mixed[]  $options
     */
    public function __construct(
        array $properties = [],
        string $type = Generator::UNDEFINED,
        array $groups = null,
        array $options = null
    ) {
        parent::__construct($properties + [
            'type' => $type,
            'groups' => $groups,
            'options' => $options,
        ]);
    }
}
