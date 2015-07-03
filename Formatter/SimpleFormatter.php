<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;

class SimpleFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        return $collection['_others'];
    }
}
