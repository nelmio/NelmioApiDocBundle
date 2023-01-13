<?php declare(strict_types=1);

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
    public function testQueryParamWithChoiceConstraintIsAddedAsEnum()
    {
        $choices = ['foo', 'bar'];

        $readerMock = $this->createMock(Reader::class);
        $readerMock->method('getMethodAnnotations')->willReturn([
            new QueryParam('my_parameter', null, new Choice([], $choices))
        ]);

        $fosRestDescriber = new FosRestDescriber($readerMock, []);
        $api = new OpenApi([]);

        $fosRestDescriber->describe(
            $api,
            new Route('/'),
            $this->createMock(\ReflectionMethod::class)
        );

        $this->assertSame($choices, $api->paths[0]->get->parameters[0]->schema->enum);
    }
}
