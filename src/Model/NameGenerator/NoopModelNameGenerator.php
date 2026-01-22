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

namespace Nelmio\ApiDocBundle\Model\NameGenerator;

use Nelmio\ApiDocBundle\Model\Model;

/**
 * Noop name generator which just returns the models `$shortName`.
 *
 * This class is used to maintain the current default behavior
 */
class NoopModelNameGenerator implements ModelNameGeneratorInterface
{
    public function generateName(Model $model, string $shortName, array $existingNames): string
    {
        return $shortName;
    }
}
