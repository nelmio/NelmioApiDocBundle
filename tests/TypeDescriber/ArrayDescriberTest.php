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
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
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

    /**
     * When the key type is arrayKey() (int|string union), the describer should
     * treat the collection as a list rather than splitting into anyOf [array, object].
     */
    public function testArrayKeyUnionIsTreatedAsList(): void
    {
        $innerDescriber = $this->createMock(TypeDescriberInterface::class);
        $innerDescriber->expects(self::once())
            ->method('describe')
            ->with(self::callback(static function (Type $type): bool {
                // Should delegate a list(string) — i.e. CollectionType with int key and string value
                return $type instanceof CollectionType
                    && $type->isList()
                    && 'string' === (string) $type->getCollectionValueType();
            }));

        $this->describer->setDescriber($innerDescriber);

        // array<string> is resolved by TypeInfo as CollectionType with arrayKey() union key
        $type = Type::array(Type::string());
        $this->describer->describe($type, new Schema([]));
    }

    /**
     * When a Traversable object is auto-wrapped in CollectionType(ObjectType),
     * the describer should unwrap it and delegate the ObjectType directly.
     */
    public function testTraversableObjectIsUnwrapped(): void
    {
        $objectType = Type::object(\ArrayObject::class);
        // Simulate what StringTypeResolver does: wrap in CollectionType(ObjectType)
        $collectionType = new CollectionType($objectType);

        $innerDescriber = $this->createMock(TypeDescriberInterface::class);
        $innerDescriber->expects(self::once())
            ->method('describe')
            ->with(self::identicalTo($objectType));

        $this->describer->setDescriber($innerDescriber);
        $this->describer->describe($collectionType, new Schema([]));
    }
}
