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

/**
 * @Annotation
 */
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
}
