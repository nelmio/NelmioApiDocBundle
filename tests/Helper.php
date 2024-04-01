<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

use PackageVersions\Versions;

final class Helper
{
    public static function isCompoundValidatorConstraintSupported(): bool
    {
        $validatorVersion = Versions::getVersion('symfony/validator');

        return version_compare($validatorVersion, 'v5.1', '>=');
    }
}
