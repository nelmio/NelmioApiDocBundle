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
use stdClass;
use Symfony\Component\PropertyInfo\Type;

class SelfDescribingModelDescriberTest extends TestCase
{
    public function testSupports()
    {
        $describer = new SelfDescribingModelDescriber();

        $this->assertTrue($describer->supports(new Model(new Type('object', false, SelfDescribingModel::class))));
    }

    public function testDoesNotSupport()
    {
        $describer = new SelfDescribingModelDescriber();

        $this->assertFalse($describer->supports(new Model(new Type('object', false, stdClass::class))));
    }

    public function testDescribe()
    {
        $describer = new SelfDescribingModelDescriber();

        $model = new Model(new Type('object', false, SelfDescribingModel::class));
        $schema = new Schema([]);

        $describer->describe($model, $schema);
        $this->assertSame('SelfDescribingTitle', $schema->title);
        $this->assertSame(SelfDescribingModel::class, $schema->description);
    }
}
