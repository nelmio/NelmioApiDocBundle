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

enum OperationIdGeneration: string
{
    /**
     * Prepends the HTTP method to the operation id.
     */
    case ALWAYS_PREPEND = 'always_prepend';
    /**
     * Checks if the operation id already starts with the HTTP method and prepends it if not.
     */
    case CONDITIONALLY_PREPEND = 'conditionally_prepend';
    /**
     * Never prepends the HTTP method to the operation id.
     * Warnings will be thrown if the operation id is not unique.
     */
    case NO_PREPEND = 'no_prepend';
}
