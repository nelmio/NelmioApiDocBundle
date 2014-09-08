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
use Nelmio\ApiDocBundle\DataTypes;

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

            $keys = array_keys($info);


            /*$newParams[$newName] = array(
                'dataType'     => $info['dataType'],
                'readonly'     => array_key_exists('readonly', $info) ? $info['readonly'] : null,
                'required'     => $info['required'],
                'default'      => array_key_exists('default', $info) ? $info['default'] : null,
                'description'  => array_key_exists('description', $info) ? $info['description'] : null,
                'format'       => array_key_exists('format', $info) ? $info['format'] : null,
                'sinceVersion' => array_key_exists('sinceVersion', $info) ? $info['sinceVersion'] : null,
                'untilVersion' => array_key_exists('untilVersion', $info) ? $info['untilVersion'] : null,
                'actualType'   => array_key_exists('actualType', $info) ? $info['actualType'] : null,
                'subType'      => array_key_exists('subType', $info) ? $info['subType'] : null,
            );*/
            $newParams[$newName] = $info;

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
        $array   = '';
        $newName = ($parentName) ? sprintf("%s[%s]", $parentName, $name) : $name;

        if (isset($data['actualType']) && $data['actualType'] == DataTypes::COLLECTION
            && isset($data['subType']) && $data['subType'] !== null
        ) {
            $array = '[]';
        }

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

        $annotation['id'] = strtolower($annotation['method']).'-'.str_replace('/', '-', $annotation['uri']);

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
