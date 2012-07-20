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

abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $annotation)
    {
        return $this->renderOne($annotation->toArray());
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

        return $this->render($array);
    }

    /**
     * Format a single array of data
     *
     * @param  array        $data
     * @return string|array
     */
    abstract protected function renderOne(array $data);

    /**
     * Format a set of resource sections.
     *
     * @param  array        $collection
     * @return string|array
     */
    abstract protected function render(array $collection);
}
