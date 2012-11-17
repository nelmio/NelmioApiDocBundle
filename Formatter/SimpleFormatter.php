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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class SimpleFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $annotation)
    {
        return $annotation->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $collection)
    {
        $array = array();
        foreach ($collection as $coll) {
            $array[$coll['resource']][] = $coll['annotation']->toArray();
        }

        return $array;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
    }
}
