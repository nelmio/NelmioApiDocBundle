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

/**
 * Compares a generated document with a fixture, ignoring component schemas nothing refers to.
 */
trait ComparesOpenApiFixturesTrait
{
    /**
     * Drops unreferenced component schemas so comparisons match across PHP/Swagger-PHP versions
     * (e.g. CleanUnusedComponents may leave different leftovers for inlined MapQueryString models).
     *
     * @return \stdClass|object
     */
    private static function normalizeOpenApiDecodedForComparison(object $openApi): object
    {
        $copy = json_decode(json_encode($openApi, \JSON_THROW_ON_ERROR), false, 512, \JSON_THROW_ON_ERROR);
        if (!isset($copy->components->schemas) || !\is_object($copy->components->schemas)) {
            return $copy;
        }

        $needed = self::collectReferencedComponentSchemaNames($copy);
        $schemas = $copy->components->schemas;
        foreach (array_keys(get_object_vars($schemas)) as $name) {
            if (!isset($needed[$name])) {
                unset($schemas->{$name});
            }
        }

        return $copy;
    }

    /**
     * @return array<string, true>
     */
    private static function collectReferencedComponentSchemaNames(object $openApi): array
    {
        $needed = [];
        $queue = [];
        $paths = $openApi->paths ?? null;
        if (\is_object($paths)) {
            foreach (self::collectSchemaRefsFromNode($paths) as $r) {
                $queue[] = $r;
            }
        }

        while ([] !== $queue) {
            $name = array_pop($queue);
            if (isset($needed[$name])) {
                continue;
            }
            $needed[$name] = true;
            if (!isset($openApi->components->schemas->{$name})) {
                continue;
            }
            foreach (self::collectSchemaRefsFromNode($openApi->components->schemas->{$name}) as $r) {
                if (!isset($needed[$r])) {
                    $queue[] = $r;
                }
            }
        }

        return $needed;
    }

    /**
     * @return list<string>
     */
    private static function collectSchemaRefsFromNode(mixed $node): array
    {
        if (\is_object($node)) {
            $refs = [];
            foreach (get_object_vars($node) as $k => $v) {
                if ('$ref' === $k && \is_string($v) && preg_match('~^#/components/schemas/(.+)$~', $v, $m)) {
                    $refs[] = $m[1];
                }
                $refs = array_merge($refs, self::collectSchemaRefsFromNode($v));
            }

            return $refs;
        }

        if (\is_array($node)) {
            $refs = [];
            foreach ($node as $v) {
                $refs = array_merge($refs, self::collectSchemaRefsFromNode($v));
            }

            return $refs;
        }

        return [];
    }

    private static function getFixture(string $fixture): string
    {
        if (!file_exists($fixture)) {
            self::fail(\sprintf('The fixture file "%s" does not exist.', $fixture));
        }

        $content = file_get_contents($fixture);

        if (false === $content) {
            self::fail(\sprintf('Failed to read the fixture file "%s".', $fixture));
        }

        return $content;
    }
}
