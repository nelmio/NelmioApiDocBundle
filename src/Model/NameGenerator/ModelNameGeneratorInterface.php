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
 * Interface to be implemented to control naming of models.
 */
interface ModelNameGeneratorInterface
{
    /**
     * Should generate a unique name for the given model.
     *
     * @param string[] $existingNames
     */
    public function generateName(Model $model, string $shortName, array $existingNames): string;
}
