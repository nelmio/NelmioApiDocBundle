<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\RouteArgumentDescriber;

use Nelmio\ApiDocBundle\Annotation\Operation;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapRequestPayloadDescriber;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\RouteArgumentDescriber\fixture\SomeObject;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\PropertyInfo\Type;

class SymfonyMapRequestPayloadDescriberTest extends TestCase
{
    public function testDescribeHandlesArrayParameterAndRegistersCorrectSchema(): void
    {
        $registry = new ModelRegistry(
            [$this->createMock(ModelDescriberInterface::class)],
            $this->createMock(OpenApi::class),
        );
        $describer = new SymfonyMapRequestPayloadDescriber();
        $describer->setModelRegistry($registry);

        $argumentData = new ArgumentMetadata(
            name: 'someObjects',
            type: Type::BUILTIN_TYPE_ARRAY,
            isVariadic: false,
            hasDefaultValue: false,
            defaultValue: null,
            attributes: [
                new MapRequestPayload(type: SomeObject::class),
            ]
        );

        $operation = $this->createMock(Operation::class);
        $operation->_context = new Context();

        $describer->describe($argumentData, $operation);

        self::assertSame('#/components/schemas/SomeObject', $operation->_context->{SymfonyMapRequestPayloadDescriber::CONTEXT_MODEL_REF});
    }
}
