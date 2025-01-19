<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Attribute;

use Nelmio\ApiDocBundle\Attribute\Areas;
use PHPUnit\Framework\TestCase;

class AreasTest extends TestCase
{
    /**
     * @dataProvider provideData
     *
     * @param string[] $areas
     */
    public function testConstruct(array $areas): void
    {
        $areasAttribute = new Areas($areas);

        foreach ($areas as $area) {
            self::assertTrue($areasAttribute->has($area));
        }
    }

    /**
     * @dataProvider provideData
     *
     * @param string[] $areas
     *
     * @group legacy
     */
    public function testDeprecatedConstruct(array $areas): void
    {
        $areasAttribute = new Areas(['value' => $areas]);

        foreach ($areas as $area) {
            self::assertTrue($areasAttribute->has($area));
        }
    }

    public static function provideData(): \Generator
    {
        yield [
            ['foo', 'bar'],
        ];

        yield [
            ['foo']
        ];

        yield [
            ['bar']
        ];
    }
}
