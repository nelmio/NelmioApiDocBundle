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
use Nelmio\ApiDocBundle\TypeDescriber\ChainDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\DateTimeDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\IntegerDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\ListDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\StringDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\UnionDescriber;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;

class UnionDescriberTest extends TestCase
{
    private ChainDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new ChainDescriber([
            new UnionDescriber(),
            new ArrayDescriber(),
            new ListDescriber(),
            new DateTimeDescriber(),
            new StringDescriber(),
            new IntegerDescriber(),
        ]);
    }

    public function testDistinctMembersAreDescribedInOneOf(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::union(Type::int(), Type::string()), $schema);

        self::assertSame(Generator::UNDEFINED, $schema->type);
        self::assertSame([['type' => 'integer'], ['type' => 'string']], self::decodeOneOf($schema));
    }

    /**
     * A oneOf with identical entries can never validate, as every value matches more than one of them.
     */
    public function testMembersDescribedIdenticallyAreNotDescribedInOneOf(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::union(Type::list(Type::string()), Type::array(Type::string())), $schema);

        self::assertSame(Generator::UNDEFINED, $schema->oneOf);
        self::assertSame(['type' => 'array', 'items' => ['type' => 'string']], json_decode($schema->toJson(), true));
    }

    public function testDateTimeClassesAreNotDescribedInOneOf(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::union(Type::object(\DateTime::class), Type::object(\DateTimeImmutable::class)), $schema);

        self::assertSame(Generator::UNDEFINED, $schema->oneOf);
        self::assertSame(['type' => 'string', 'format' => 'date-time'], json_decode($schema->toJson(), true));
    }

    public function testOnlyTheFirstOfIdenticallyDescribedMembersIsKeptInOneOf(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::union(Type::list(Type::int()), Type::array(Type::string()), Type::array(Type::int())), $schema);

        self::assertSame([
            ['type' => 'array', 'items' => ['type' => 'integer']],
            ['type' => 'array', 'items' => ['type' => 'string']],
        ], self::decodeOneOf($schema));
    }

    /**
     * @return list<mixed>
     */
    private static function decodeOneOf(Schema $schema): array
    {
        self::assertIsArray($schema->oneOf);

        return array_map(static fn (Schema $childSchema): mixed => json_decode($childSchema->toJson(), true), $schema->oneOf);
    }
}
