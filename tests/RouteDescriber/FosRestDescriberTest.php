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

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Nelmio\ApiDocBundle\RouteDescriber\FosRestDescriber;
use OpenApi\Annotations\OpenApi;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Route;
use Symfony\Component\Validator\Constraints\Choice;

class FosRestDescriberTest extends TestCase
{
    public function testQueryParamWithChoiceConstraintIsAddedAsEnum(): void
    {
        $choices = ['foo', 'bar'];

        $queryParam = new QueryParam();
        $queryParam->requirements = new Choice($choices);

        $readerMock = null;
        if (interface_exists(Reader::class)) {
            $readerMock = $this->createMock(Reader::class);
            $readerMock->method('getMethodAnnotations')->willReturn([
                $queryParam,
            ]);
        }

        $fosRestDescriber = new FosRestDescriber($readerMock, []);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $this->createMock(\ReflectionMethod::class)
        );

        self::assertSame($choices, $api->paths[0]->get->parameters[0]->schema->enum);
    }
}
