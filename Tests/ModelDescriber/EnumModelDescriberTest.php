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
use Nelmio\ApiDocBundle\ModelDescriber\EnumModelDescriber;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures\EnumModelBackedInt;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures\EnumModelBackedString;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures\EnumModelPure;
use Nelmio\ApiDocBundle\Tests\ModelDescriber\Fixtures\SelfDescribingModel;
use OpenApi\Annotations\Schema;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;
use function var_dump;

class EnumModelDescriberTest extends TestCase
{
    public function testSupports()
    {
        $describer = new EnumModelDescriber();

        $this->assertTrue($describer->supports(new Model(new Type('object', false, EnumModelPure::class))));
    }

    public function testDoesNotSupport()
    {
        $describer = new EnumModelDescriber();

        $this->assertFalse($describer->supports(new Model(new Type('object', false, \stdClass::class))));
    }

    public function testDescribeModelPure()
    {
        $describer = new EnumModelDescriber();

        $model = new Model(new Type('object', false, EnumModelPure::class));
        $schema = new Schema([]);

        $describer->describe($model, $schema);

        $this->assertSame('string', $schema->type);
        $this->assertSame(['VALUE'], $schema->enum);
    }

    public function testDescribeModelBackedInt()
    {
        $describer = new EnumModelDescriber();

        $model = new Model(new Type('object', false, EnumModelBackedInt::class));
        $schema = new Schema([]);

        $describer->describe($model, $schema);

        $this->assertSame('integer', $schema->type);
        $this->assertSame([1], $schema->enum);
    }

    public function testDescribeModelBackedString()
    {
        $describer = new EnumModelDescriber();

        $model = new Model(new Type('object', false, EnumModelBackedString::class));
        $schema = new Schema([]);

        $describer->describe($model, $schema);

        $this->assertSame('string', $schema->type);
        $this->assertSame(['value'], $schema->enum);
    }
}
