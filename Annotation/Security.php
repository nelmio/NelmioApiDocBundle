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

use Swagger\Annotations\AbstractAnnotation;

/**
 * @Annotation
 */
class Security extends AbstractAnnotation
{
    /** {@inheritdoc} */
    public static $_types = [
        'name' => 'string',
    ];

    public static $_required = ['name'];

    /**
     * @var string
     */
    public $name;
}
