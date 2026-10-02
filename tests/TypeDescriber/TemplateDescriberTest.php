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

use Nelmio\ApiDocBundle\TypeDescriber\ChainDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\IntegerDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\MixedDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\StringDescriber;
use Nelmio\ApiDocBundle\TypeDescriber\TemplateDescriber;
use OpenApi\Annotations\Schema;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\TypeInfo\Type;

class TemplateDescriberTest extends TestCase
{
    private ChainDescriber $describer;

    protected function setUp(): void
    {
        $this->describer = new ChainDescriber([
            new TemplateDescriber(),
            new StringDescriber(),
            new IntegerDescriber(),
            new MixedDescriber(),
        ]);
    }

    public function testSupportsTemplateTypeWithoutResolvedTemplates(): void
    {
        self::assertTrue((new TemplateDescriber())->supports(Type::template('T')));
    }

    public function testDoesNotSupportNonTemplateType(): void
    {
        /* @phpstan-ignore argument.type (the chain dispatches every type, not only template types) */
        self::assertFalse((new TemplateDescriber())->supports(Type::string()));
    }

    public function testDescribeUsesTheResolvedTemplate(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::template('T', Type::string()), $schema, [
            TemplateDescriber::TEMPLATES_KEY => ['T' => Type::int()],
        ]);

        self::assertSame('integer', $schema->type);
    }

    public function testDescribeFallsBackToTheBoundWithoutResolvedTemplates(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::template('T', Type::string()), $schema);

        self::assertSame('string', $schema->type);
    }

    public function testDescribeFallsBackToTheBoundWhenTheTemplateIsNotResolved(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::template('T', Type::string()), $schema, [
            TemplateDescriber::TEMPLATES_KEY => ['U' => Type::int()],
        ]);

        self::assertSame('string', $schema->type);
    }

    public function testDescribeFallsBackToMixedForAnUnboundedTemplate(): void
    {
        $schema = new Schema([]);

        $this->describer->describe(Type::template('T'), $schema);

        self::assertSame(Generator::UNDEFINED, $schema->type);
    }
}
