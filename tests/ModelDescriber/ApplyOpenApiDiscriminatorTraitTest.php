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
use OpenApi\Context;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ApplyOpenApiDiscriminatorTraitTest extends TestCase
{
    use ApplyOpenApiDiscriminatorTrait;

    public const GROUPS = ['test'];
    public const OPTIONS = ['test' => 123];

    private OA\Schema $schema;

    private Model $model;

    private ModelRegistry $modelRegistry;

    public function testApplyAddsDiscriminatorProperty(): void
    {
        $this->applyOpenApiDiscriminator($this->model, $this->schema, $this->modelRegistry, 'type', [
            'one' => 'FirstType',
            'two' => 'SecondType',
        ]);

        self::assertInstanceOf(OA\Discriminator::class, $this->schema->discriminator);
        self::assertSame('type', $this->schema->discriminator->propertyName);
        self::assertArrayHasKey('one', $this->schema->discriminator->mapping);
        self::assertSame(
            $this->modelRegistry->register($this->createModel('FirstType')),
            $this->schema->discriminator->mapping['one']
        );
        self::assertArrayHasKey('two', $this->schema->discriminator->mapping);
        self::assertSame(
            $this->modelRegistry->register($this->createModel('SecondType')),
            $this->schema->discriminator->mapping['two']
        );
    }

    public function testApplyAddsOneOfFieldToSchema(): void
    {
        $this->applyOpenApiDiscriminator($this->model, $this->schema, $this->modelRegistry, 'type', [
            'one' => 'FirstType',
            'two' => 'SecondType',
        ]);

        self::assertNotSame(Generator::UNDEFINED, $this->schema->oneOf);
        self::assertCount(2, $this->schema->oneOf);
        self::assertSame(
            $this->modelRegistry->register($this->createModel('FirstType')),
            $this->schema->oneOf[0]->ref
        );
        self::assertSame(
            $this->modelRegistry->register($this->createModel('SecondType')),
            $this->schema->oneOf[1]->ref
        );
    }

    protected function setUp(): void
    {
        $this->schema = new OA\Schema(['_context' => new Context()]);
        $this->model = $this->createModel(__CLASS__);
        $this->modelRegistry = new ModelRegistry([], new OA\OpenApi(['_context' => new Context()]));
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
