<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle;

/**
 * All the supported data-types which will be specified in the `actualType` properties in parameters.
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class DataTypes
{
    const INTEGER    = 'integer';

    const FLOAT      = 'float';

    const STRING     = 'string';

    const BOOLEAN    = 'boolean';

    const FILE       = 'file';

    const ENUM       = 'choice';

    const COLLECTION = 'collection';

    const MODEL      = 'model';

    const DATE       = 'date';

    const DATETIME   = 'datetime';

    const TIME       = 'time';

    /**
     * Returns true if the supplied `actualType` value is considered a primitive type. Returns false, otherwise.
     *
     * @param  string $type
     * @return bool
     */
    public static function isPrimitive($type)
    {
        return in_array(strtolower($type), array(
            static::INTEGER,
            static::FLOAT,
            static::STRING,
            static::BOOLEAN,
            static::FILE,
            static::DATE,
            static::DATETIME,
            static::TIME,
            static::ENUM,
        ));
    }
}
