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
 * Name generator which appends group names to model short name.
 *
 * Examples:
 * - Model: "User", Groups: null → "User"
 * - Model: "User", Groups: [] → "User"
 * - Model: "User", Groups: ["api"] → "User_api"
 * - Model: "User", Groups: ["api", "admin"] → "User_admin_api" (sorted alphabetically)
 * - Model: "User", Groups: ["api-v1", "admin_panel"] → "User_adminPanel_apiV1"
 * - Model: "Product", Groups: ["public API", "read-only"] → "Product_publicApi_readOnly"
 *
 * Groups are sanitized to camelCase format and sorted alphabetically before concatenation.
 */
readonly class GroupAppendingModelNameGenerator implements ModelNameGeneratorInterface
{
    /**
     * The default separator used when concatenating groups and short name.
     */
    public const DEFAULT_GROUP_SEPARATOR = '_';

    public function __construct(
        /**
         * The separator used to concatenate groups and short name.
         */
        private string $groupSeparator = self::DEFAULT_GROUP_SEPARATOR,
    ) {
    }

    public function generateName(Model $model, string $shortName, array $existingNames): string
    {
        $groupsRaw = $model->getGroups() ?? [];

        if (0 === \count($groupsRaw)) {
            return $shortName;
        }

        $groups = array_map([$this, 'sanitizeGroupName'], $groupsRaw);

        usort($groups, 'strcmp');

        return $shortName.$this->groupSeparator.implode($this->groupSeparator, $groups);
    }

    /**
     * Sanitizes group name into a schema friendly format.
     *
     * Removes all non-alphanumeric characters and joins the string in camelCase format.
     */
    private function sanitizeGroupName(string $string): string
    {
        $parts = preg_split('/[^A-Za-z0-9]+/', $string, -1, \PREG_SPLIT_NO_EMPTY);

        if (false === $parts) {
            return '';
        }

        // transform first part to lowercase
        $first = strtolower(array_shift($parts));

        // others to first uppercase
        foreach ($parts as &$part) {
            $part = ucfirst(strtolower($part));
        }

        return $first.implode('', $parts);
    }
}
