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
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ApplyOpenApiDiscriminatorTrait;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ApplyOpenApiDiscriminatorTraitTest extends TestCase
{
    use ApplyOpenApiDiscriminatorTrait;

    const GROUPS = ['test'];
    const OPTIONS = ['test' => 123];

    private $schema;

    private $model;

    public function testApplyAddsDiscriminatorProperty()
    {
        $this->applyOpenApiDiscriminator($this->model, $this->schema, $this->modelRegistry, 'type', [
            'one' => 'FirstType',
            'two' => 'SecondType',
        ]);

        $this->assertInstanceOf(OA\Discriminator::class, $this->schema->discriminator);
        $this->assertSame('type', $this->schema->discriminator->propertyName);
        $this->assertArrayHasKey('one', $this->schema->discriminator->mapping);
        $this->assertSame(
            $this->modelRegistry->register($this->createModel('FirstType')),
            $this->schema->discriminator->mapping['one']
        );
        $this->assertArrayHasKey('two', $this->schema->discriminator->mapping);
        $this->assertSame(
            $this->modelRegistry->register($this->createModel('SecondType')),
            $this->schema->discriminator->mapping['two']
        );
    }

    public function testApplyAddsOneOfFieldToSchema()
    {
        $this->applyOpenApiDiscriminator($this->model, $this->schema, $this->modelRegistry, 'type', [
            'one' => 'FirstType',
            'two' => 'SecondType',
        ]);

        $this->assertNotSame(OA\UNDEFINED, $this->schema->oneOf);
        $this->assertCount(2, $this->schema->oneOf);
        $this->assertSame(
            $this->modelRegistry->register($this->createModel('FirstType')),
            $this->schema->oneOf[0]->ref
        );
        $this->assertSame(
            $this->modelRegistry->register($this->createModel('SecondType')),
            $this->schema->oneOf[1]->ref
        );
    }

    protected function setUp(): void
    {
        $this->schema = new OA\Schema([]);
        $this->model = $this->createModel(__CLASS__);
        $this->modelRegistry = new ModelRegistry([], new OA\OpenApi([]));
    }

    private function createModel(string $className): Model
    {
        return new Model(
            new Type(Type::BUILTIN_TYPE_OBJECT, false, $className),
            self::GROUPS,
            self::OPTIONS
        );
    }
}
