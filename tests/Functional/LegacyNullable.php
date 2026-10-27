<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Composer\InstalledVersions;

/**
 * Since swagger-php 6.14 (zircote/swagger-php#2251), a 3.0 schema no longer carries `nullable`
 * without a `type` beside it: a `oneOf` or `anyOf` gets a branch that matches only `null`, and
 * anywhere else `nullable` is dropped.
 *
 * The fixtures follow that. This rewrites the output of older swagger-php versions the same way,
 * so one set of fixtures holds for every supported version. Remove it once the floor is ^6.14.
 */
final class LegacyNullable
{
    private const NULL_SCHEMA = ['type' => 'object', 'nullable' => true, 'enum' => [null]];

    public static function upgrade(mixed $node): mixed
    {
        if (!self::isLegacy()) {
            return $node;
        }

        return self::rewrite($node);
    }

    public static function upgradeJson(string $json): string
    {
        if (!self::isLegacy()) {
            return $json;
        }

        $node = self::rewrite(json_decode($json, false, 512, \JSON_THROW_ON_ERROR));

        return json_encode($node, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR);
    }

    private static function isLegacy(): bool
    {
        return version_compare(InstalledVersions::getVersion('zircote/swagger-php') ?? '0', '6.14.0', '<');
    }

    private static function rewrite(mixed $node): mixed
    {
        if (\is_object($node)) {
            foreach (get_object_vars($node) as $key => $value) {
                $node->{$key} = self::rewrite($value);
            }

            if (true === ($node->nullable ?? null) && !isset($node->type)) {
                if (isset($node->oneOf)) {
                    $node->oneOf[] = (object) self::NULL_SCHEMA;
                    unset($node->nullable);
                } elseif (isset($node->anyOf)) {
                    $node->anyOf[] = (object) self::NULL_SCHEMA;
                    unset($node->nullable);
                } elseif (!isset($node->allOf)) {
                    unset($node->nullable);
                }
            }

            return $node;
        }

        if (\is_array($node)) {
            $node = array_map(self::rewrite(...), $node);

            if (true === ($node['nullable'] ?? null) && !isset($node['type'])) {
                if (isset($node['oneOf'])) {
                    $node['oneOf'][] = self::NULL_SCHEMA;
                    unset($node['nullable']);
                } elseif (isset($node['anyOf'])) {
                    $node['anyOf'][] = self::NULL_SCHEMA;
                    unset($node['nullable']);
                } elseif (!isset($node['allOf'])) {
                    unset($node['nullable']);
                }
            }
        }

        return $node;
    }
}
