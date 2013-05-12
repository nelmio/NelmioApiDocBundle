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
        return $this->renderOne(
            $this->processAnnotation($annotation->toArray())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $collection)
    {
        return $this->render(
            $this->processCollection($collection)
        );
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

    /**
     * Compresses nested parameters into a flat by changing the parameter
     * names to strings which contain the nested property names, for example:
     * `user[group][name]`
     *
     *
     * @param  array   $data
     * @param  string  $parentName
     * @param  boolean $ignoreNestedReadOnly
     * @return array
     */
    protected function compressNestedParameters(array $data, $parentName = null, $ignoreNestedReadOnly = false)
    {
        $newParams = array();
        foreach ($data as $name => $info) {
            $newName = $this->getNewName($name, $info, $parentName);

            $newParams[$newName] = array(
                'description'   => $info['description'],
                'dataType'      => $info['dataType'],
                'readonly'      => $info['readonly'],
                'required'      => $info['required'],
                'sinceVersion'  => array_key_exists('sinceVersion', $info) ? $info['sinceVersion'] : null,
                'untilVersion'  => array_key_exists('untilVersion', $info) ? $info['untilVersion'] : null,
            );

            if (isset($info['children']) && (!$info['readonly'] || !$ignoreNestedReadOnly)) {
                foreach ($this->compressNestedParameters($info['children'], $newName, $ignoreNestedReadOnly) as $nestedItemName => $nestedItemData) {
                    $newParams[$nestedItemName] = $nestedItemData;
                }
            }
        }

        return $newParams;
    }

    /**
     * Returns a new property name, taking into account whether or not the property
     * is an array of some other data type.
     *
     * @param  string $name
     * @param  array  $data
     * @param  string $parentName
     * @return string
     */
    protected function getNewName($name, $data, $parentName = null)
    {
        $newName = ($parentName) ? sprintf("%s[%s]", $parentName, $name) : $name;
        $array   = (false === strpos($data['dataType'], "array of")) ? "" : "[]";

        return sprintf("%s%s", $newName, $array);
    }

    /**
     * @param  array $annotation
     * @return array
     */
    protected function processAnnotation($annotation)
    {
        if (isset($annotation['parameters'])) {
            $annotation['parameters'] = $this->compressNestedParameters($annotation['parameters'], null, true);
        }

        if (isset($annotation['response'])) {
            $annotation['response'] = $this->compressNestedParameters($annotation['response']);
        }

        return $annotation;
    }

    /**
     * @param  array[ApiDoc] $collection
     * @return array
     */
    protected function processCollection(array $collection)
    {
        $array = array();
        foreach ($collection as $coll) {
            $array[$coll['annotation']->getSection()][$coll['resource']][] = $coll['annotation']->toArray();
        }

        $processedCollection = array();
        foreach ($array as $section => $resources) {
            foreach ($resources as $path => $annotations) {
                foreach ($annotations as $annotation) {
                    if ($section) {
                        $processedCollection[$section][$path][] = $this->processAnnotation($annotation);
                    } else {
                        $processedCollection['_others'][$path][] = $this->processAnnotation($annotation);
                    }
                }
            }
        }

        ksort($processedCollection);

        return $processedCollection;
    }
}
