<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace
{
    if (PHP_VERSION_ID < 50400) {

        define('JSON_UNESCAPED_UNICODE', 256);
        /**
         * This stub must be removed when the PHP version is bumped to >= 5.4
         *
         * Objects implementing JsonSerializable
         * can customize their JSON representation when encoded with
         * `json_encode()`
         * .
         * @see http://php.net/manual/en/class.jsonserializable.php
         */
        interface JsonSerializable
        {
            /**
             * (PHP 5 >= 5.4.0, PHP 7)
             * Specify data which should be serialized to JSON
             *
             * @see http://php.net/manual/en/jsonserializable.jsonserialize.php
             *
             * @return mixed data which can be serialized by `json_encode()`,
             *               which is a value of any type other than a resource
             */
            public function jsonSerialize();
        }
    }
}
