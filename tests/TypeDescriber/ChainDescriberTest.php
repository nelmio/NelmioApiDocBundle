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

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\TypeDescriber\ChainDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\NullableDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\StoppableTypeDescriberInterface;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberAwareInterface;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberAwareTrait;
use Nelmio\ApiDocBundle\TypeDescriber\TypeDescriberInterface;
use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;

class ChainDescriberTest extends TestCase
{
    /**
     * Records the names of the describers that were invoked, in order.
     *
     * @var \ArrayObject<int, string>
     */
    private \ArrayObject $log;

    protected function setUp(): void
    {
        $this->log = new \ArrayObject();
    }

    public function testDescribeRunsEveryMatchingDescriber(): void
    {
        $chain = new ChainDescriber([
            $this->createDescriber('first'),
            $this->createDescriber('notMatching', false),
            $this->createDescriber('second'),
        ]);

        $chain->describe(Type::string(), new Schema([]));

        self::assertSame(['first', 'second'], $this->log->getArrayCopy());
    }

    public function testDescribeStopsAfterAMatchingStoppableDescriber(): void
    {
        $chain = new ChainDescriber([
            $this->createStoppableDescriber('stoppable'),
            $this->createDescriber('later'),
        ]);

        $chain->describe(Type::string(), new Schema([]));

        self::assertSame(['stoppable'], $this->log->getArrayCopy());
    }

    public function testDescribeDoesNotStopForANonMatchingStoppableDescriber(): void
    {
        $chain = new ChainDescriber([
            $this->createStoppableDescriber('stoppable', false),
            $this->createDescriber('later'),
        ]);

        $chain->describe(Type::string(), new Schema([]));

        self::assertSame(['later'], $this->log->getArrayCopy());
    }

    public function testDescribeWithAStoppableDescriberLastBehavesLikeANonStoppableOne(): void
    {
        $chain = new ChainDescriber([
            $this->createDescriber('first'),
            $this->createStoppableDescriber('stoppable'),
        ]);

        $chain->describe(Type::string(), new Schema([]));

        self::assertSame(['first', 'stoppable'], $this->log->getArrayCopy());
    }

    public function testDescribeCombinesANonStoppableDescriberWithTheNullableDescriber(): void
    {
        $chain = new ChainDescriber([
            $this->createDescriber('describer'),
            new NullableDescriber(),
        ]);

        $schema = new Schema([]);
        $chain->describe(Type::nullable(Type::string()), $schema);

        self::assertSame(['describer'], $this->log->getArrayCopy());
        self::assertTrue($schema->nullable);
    }

    public function testDescribeWithAStoppableDescriberMatchingANullableTypeLosesNullable(): void
    {
        $chain = new ChainDescriber([
            $this->createStoppableDescriber('stoppable'),
            new NullableDescriber(),
        ]);

        $schema = new Schema([]);
        $chain->describe(Type::nullable(Type::string()), $schema);

        self::assertSame(['stoppable'], $this->log->getArrayCopy());
        self::assertSame(Generator::UNDEFINED, $schema->nullable);
    }

    public function testSupportsReturnsTrueWhenOnlyALaterDescriberMatches(): void
    {
        $chain = new ChainDescriber([
            $this->createDescriber('notMatching', false),
            $this->createStoppableDescriber('matching'),
        ]);

        self::assertTrue($chain->supports(Type::string()));
    }

    public function testSupportsReturnsFalseWhenNoDescriberMatches(): void
    {
        $chain = new ChainDescriber([
            $this->createDescriber('notMatching', false),
            $this->createStoppableDescriber('alsoNotMatching', false),
        ]);

        self::assertFalse($chain->supports(Type::string()));
    }

    public function testSupportsReturnsFalseWithoutDescribers(): void
    {
        self::assertFalse((new ChainDescriber([]))->supports(Type::string()));
    }

    public function testDescribePropagatesTheModelRegistryAndTheChainItself(): void
    {
        $describer = new class implements TypeDescriberInterface, ModelRegistryAwareInterface, TypeDescriberAwareInterface {
            use ModelRegistryAwareTrait;
            use TypeDescriberAwareTrait;

            public ?ModelRegistry $injectedModelRegistry = null;
            public ?TypeDescriberInterface $injectedDescriber = null;

            public function describe(Type $type, Schema $schema, array $context = []): void
            {
                $this->injectedModelRegistry = $this->modelRegistry;
                $this->injectedDescriber = $this->describer;
            }

            public function supports(Type $type, array $context = []): bool
            {
                return true;
            }
        };

        $modelRegistry = new ModelRegistry([], new OpenApi(['_context' => Util::createContext()]));

        $chain = new ChainDescriber([$describer]);
        $chain->setModelRegistry($modelRegistry);
        $chain->describe(Type::string(), new Schema([]));

        self::assertSame($modelRegistry, $describer->injectedModelRegistry);
        self::assertSame($chain, $describer->injectedDescriber);
    }

    private function createDescriber(string $name, bool $supports = true): TypeDescriberInterface
    {
        return new class($this->log, $name, $supports) implements TypeDescriberInterface {
            /**
             * @param \ArrayObject<int, string> $log
             */
            public function __construct(
                private \ArrayObject $log,
                private string $name,
                private bool $supports,
            ) {
            }

            public function describe(Type $type, Schema $schema, array $context = []): void
            {
                $this->log[] = $this->name;
            }

            public function supports(Type $type, array $context = []): bool
            {
                return $this->supports;
            }
        };
    }

    private function createStoppableDescriber(string $name, bool $supports = true): StoppableTypeDescriberInterface
    {
        return new class($this->log, $name, $supports) implements StoppableTypeDescriberInterface {
            /**
             * @param \ArrayObject<int, string> $log
             */
            public function __construct(
                private \ArrayObject $log,
                private string $name,
                private bool $supports,
            ) {
            }

            public function describe(Type $type, Schema $schema, array $context = []): void
            {
                $this->log[] = $this->name;
            }

            public function supports(Type $type, array $context = []): bool
            {
                return $this->supports;
            }
        };
    }
}
