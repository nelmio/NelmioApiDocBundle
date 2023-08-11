<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber;

use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelDescriber;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonyDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use const PHP_VERSION_ID;

class SymfonyDescriberTest extends TestCase
{
    private $symfonyDescriber;

    protected function setUp(): void
    {
        if (PHP_VERSION_ID < 80100) {
            self::markTestSkipped('Attributes require PHP 8');
        }

        if (
            !class_exists(MapRequestPayload::class)
            && !class_exists(MapQueryParameter::class)
        ) {
            self::markTestSkipped('Symfony 6.3 attributes not found');
        }

        $registry = new ModelRegistry(
            [new SelfDescribingModelDescriber()],
            new OpenApi(['_context' => new Context()]),
            []
        );

        $this->symfonyDescriber = new SymfonyDescriber();

        $this->symfonyDescriber->setModelRegistry($registry);
    }
}
