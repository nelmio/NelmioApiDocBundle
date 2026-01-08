<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\TypeDescriber;

use Nelmio\ApiDocBundle\TypeDescriber\ArrayDescriber;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;
use Symfony\Component\TypeInfo\Type\CollectionType;
use Symfony\Component\TypeInfo\Type\UnionType;

class ArrayDescriberTest extends TestCase
{
    private ArrayDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new ArrayDescriber();
    }

    /**
     * @dataProvider provideInvalidCollectionTypes
     *
     * @param CollectionType $type
     */
    public function testDescribeHandlesInvalidKeyType($type): void
    {
        self::expectException(\LogicException::class);
        self::expectExceptionMessage('This describer only supports '.CollectionType::class.' with '.UnionType::class.' as key type.');

        $this->describer->describe($type, new Schema([]));
    }

    public static function provideInvalidCollectionTypes(): \Generator
    {
        yield [Type::array(Type::int(), Type::int())];
        yield [Type::array(Type::int(), Type::string())];
        yield [Type::list()];
        yield [Type::dict()];
    }
}
