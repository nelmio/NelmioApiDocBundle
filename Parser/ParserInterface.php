<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Parser;

/**
 * This is the interface parsers must implement in order to be registered in the ApiDocExtractor.
 */
interface ParserInterface
{
    /**
     * Return true/false whether this class supports parsing the given class.
     *
     * @param  string  $item The string type of input to parse.
     * @return boolean
     */
    public function supports($item);

    /**
     * Returns an array of class property metadata where each item is a key (the property name) and
     * an array of data with the following keys:
     *
     *  - dataType          string - this value should be one of the following data type descriptions:
     *                          - integer
     *                          - boolean
     *                          - string
     *                          - double
     *                          - DateTime
     *                          - T             - A fully qualified class name, such as "My\Namespaced\Object"
     *                          - array         - A loosely defined key/val hash
     *                          - array<T>      - An array of objects with a fully qualified class name
     *                          - array<K,T>    - An array with keys of a specific type (string|integer) and values of a specific type
     *  - required          boolean
     *  - description       string
     *  - readonly          boolean
     *  - children          (optional) array of nested property names mapped to arrays
     *                      in the format described here
     *
     * @param  string $item The string type of input to parse.  This is most likely a class name.
     * @return array
     */
    public function parse($item);

}
