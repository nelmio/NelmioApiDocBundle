<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures\SelfDescribingModel;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type as LegacyType;
use Symfony\Component\TypeInfo\Type;

class SelfDescribingModelDescriberTest extends TestCase
{
    public function testSupports(): void
    {
        $describer = new SelfDescribingModelDescriber();

        if (class_exists(LegacyType::class)) {
            self::assertTrue($describer->supports(new Model(new LegacyType('object', false, SelfDescribingModel::class))));
        } else {
            self::assertTrue($describer->supports(new Model(Type::object(SelfDescribingModel::class))));
        }
    }

    public function testDoesNotSupport(): void
    {
        $describer = new SelfDescribingModelDescriber();

        if (class_exists(LegacyType::class)) {
            self::assertFalse($describer->supports(new Model(new LegacyType('object', false, \stdClass::class))));
        } else {
            self::assertFalse($describer->supports(new Model(Type::object(\stdClass::class))));
        }
    }

    public function testDescribe(): void
    {
        $describer = new SelfDescribingModelDescriber();

        $model = class_exists(LegacyType::class)
            ? new Model(new LegacyType('object', false, SelfDescribingModel::class))
            : new Model(Type::object(SelfDescribingModel::class));
        $schema = new Schema([]);

        $describer->describe($model, $schema);
        self::assertSame('SelfDescribingTitle', $schema->title);
        self::assertSame(SelfDescribingModel::class, $schema->description);
    }
}
