<?php
/**
 * Created by PhpStorm.
 * User: bezalelhermoso
 * Date: 6/16/14
 * Time: 11:54 AM
 */

namespace Nelmio\ApiDocBundle;


/**
 * Class DataTypes
 *
 * @package Nelmio\ApiDocBundle
 * @author Bez Hermoso <bez@activelamp.com>
 */
class DataTypes 
{
    const INTEGER = 'integer';

    const FLOAT = 'float';

    const STRING = 'string';

    const BOOLEAN = 'boolean';

    const BYTE = 'byte';

    const ENUM = 'choice';

    const COLLECTION = 'collection';

    const NATIVE_ARRAY = 'array';

    const MODEL = 'model';

    const DATE = 'date';

    const DATETIME = 'datetime';

    const TIME = 'time';

    public static function isPrimitive($type)
    {
        return in_array($type, array(
            static::INTEGER,
            static::FLOAT,
            static::STRING,
            static::BOOLEAN,
            static::BYTE,
            static::NATIVE_ARRAY,
            static::DATE,
            static::DATETIME,
            static::TIME,
        ));
    }
}