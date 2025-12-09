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

use Nelmio\ApiDocBundle\Attribute\Operation;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\RouteDescriber\RouteArgumentDescriber\SymfonyMapRequestPayloadDescriber;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\RouteArgumentDescriber\fixture\SomeObject;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestPayloadValueResolver;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class SymfonyMapRequestPayloadDescriberTest extends TestCase
{
    public function testDescribeHandlesArrayParameterAndRegistersCorrectSchema(): void
    {
        if (!class_exists(MapRequestPayload::class)) {
            self::markTestSkipped('Requires Symfony 7.1');
        }

        $attribute = new \ReflectionClass(MapRequestPayload::class);
        if (!$attribute->hasProperty('type')) {
            self::markTestSkipped('Requires Symfony 7.1');
        }

        $registry = new ModelRegistry(
            [$this->createMock(ModelDescriberInterface::class)],
            $this->createMock(OpenApi::class),
        );
        $describer = new SymfonyMapRequestPayloadDescriber();
        $describer->setModelRegistry($registry);

        $argumentData = new ArgumentMetadata(
            'someObjects',
            'array',
            false,
            false,
            null,
            false,
            [
                new MapRequestPayload(
                    null,
                    [],
                    null,
                    RequestPayloadValueResolver::class,
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    SomeObject::class
                ),
            ]
        );

        $operation = new Operation([]);
        $operation->_context = new Context();

        $describer->describe($argumentData, $operation);

        self::assertSame('#/components/schemas/SomeObject', $operation->_context->{SymfonyMapRequestPayloadDescriber::CONTEXT_MODEL_REF});
    }
}
