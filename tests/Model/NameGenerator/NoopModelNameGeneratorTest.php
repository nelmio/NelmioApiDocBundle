<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Model\NameGenerator;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\NameGenerator\NoopModelNameGenerator;
use PHPUnit\Framework\TestCase;

final class NoopModelNameGeneratorTest extends TestCase
{
    public function testGenerateReturnsInputName(): void
    {
        $input = 'Foo';

        $generator = new NoopModelNameGenerator();
        $model = new Model($this->createMock(\Symfony\Component\PropertyInfo\Type::class), []);

        $output = $generator->generateName($model, $input, []);

        self::assertSame($input, $output);
    }
}
