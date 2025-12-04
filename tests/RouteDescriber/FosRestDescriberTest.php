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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\RouteDescriber\FosRestDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraints\Choice;

#[Group('fos-rest')]
class FosRestDescriberTest extends TestCase
{
    public function testQueryParamWithChoiceConstraintIsAddedAsEnum(): void
    {
        $class = new class {
            #[QueryParam(requirements: new Choice(choices: ['foo', 'bar']))]
            public function getAction(): void
            {
            }
        };
        $reflectionMethod = new \ReflectionMethod($class, 'getAction');

        $fosRestDescriber = new FosRestDescriber([]);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $reflectionMethod,
        );

        self::assertSame(['foo', 'bar'], $api->paths[0]->get->parameters[0]->schema->enum);
    }

    public function testQueryParamWithChoiceConstraintCallbackIsAddedAsEnum(): void
    {
        $class = new class {
            #[QueryParam(requirements: new Choice(callback: 'getChoices'))]
            public function getAction(): void
            {
            }

            /**
             * @return string[]
             */
            public static function getChoices(): array
            {
                return ['foo', 'bar'];
            }
        };
        $reflectionMethod = new \ReflectionMethod($class, 'getAction');

        $fosRestDescriber = new FosRestDescriber([]);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $reflectionMethod,
        );

        self::assertSame(['foo', 'bar'], $api->paths[0]->get->parameters[0]->schema->enum);
    }

    public function testQueryParamWithChoiceConstraintAsArray(): void
    {
        $class = new class {
            #[QueryParam(requirements: new Choice(['foo', 'bar'], multiple: true))]
            public function getAction(): void
            {
            }
        };

        $reflectionMethod = new \ReflectionMethod($class, 'getAction');

        $fosRestDescriber = new FosRestDescriber([]);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $reflectionMethod,
        );

        self::assertEquals('array', $api->paths[0]->get->parameters[0]->schema->type);
        self::assertSame(['foo', 'bar'], $api->paths[0]->get->parameters[0]->schema->items->enum);
    }

    public function testQueryParamWithInvalidCallback(): void
    {
        $class = new class {
            #[QueryParam(requirements: new Choice(callback: 'InvalidClass::getChoices'))]
            public function getAction(): void
            {
            }
        };
        $reflectionMethod = new \ReflectionMethod($class, 'getAction');

        $fosRestDescriber = new FosRestDescriber([]);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $reflectionMethod,
        );

        self::assertSame(Generator::UNDEFINED, $api->paths[0]->get->parameters[0]->schema->enum);
    }
}
