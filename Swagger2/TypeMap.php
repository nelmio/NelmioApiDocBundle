<?php

namespace Nelmio\ApiDocBundle\Swagger2;

use Nelmio\ApiDocBundle\DataTypes;

class TypeMap
{

    protected static $typeMap = array(
        DataTypes::INTEGER => 'integer',
        DataTypes::FLOAT => 'number',
        DataTypes::STRING => 'string',
        DataTypes::BOOLEAN => 'boolean',
        DataTypes::FILE => 'string',
        DataTypes::DATE => 'string',
        DataTypes::DATETIME => 'string',
    );

    protected static $formatMap = array(
        DataTypes::INTEGER => 'int32',
        DataTypes::FLOAT => 'float',
        DataTypes::FILE => 'byte',
        DataTypes::DATE => 'date',
        DataTypes::DATETIME => 'date-time',
    );

    public static function type($key, $default = null)
    {
        return isset(static::$typeMap[$key]) ? static::$typeMap[$key] : $default;
    }

    public static function format($key, $default = null)
    {
        return isset(static::$formatMap[$key]) ? static::$formatMap[$key] : $default;
    }
}
