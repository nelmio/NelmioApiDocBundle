<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests;

use PackageVersions\Versions;

final class Helper
{
    public static function isCompoundValidatorConstraintSupported(): bool
    {
        $validatorVersion = Versions::getVersion('symfony/validator');

        return version_compare($validatorVersion, 'v5.1', '>=');
    }

    public static function isDoctrineAnnotationsAvailable(): bool
    {
        try {
            Versions::getVersion('doctrine/annotations');

            return true;
        } catch (\OutOfBoundsException $e) {
            return false;
        }
    }
}
