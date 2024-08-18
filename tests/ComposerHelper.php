<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

final class ComposerHelper
{
    public static function compareVersion(string $package, string $version): ?int
    {
        return version_compare(\Composer\InstalledVersions::getPrettyVersion($package), $version);
    }

    public static function isPackageInstalled(string $package): bool
    {
        return \Composer\InstalledVersions::isInstalled($package);
    }
}
