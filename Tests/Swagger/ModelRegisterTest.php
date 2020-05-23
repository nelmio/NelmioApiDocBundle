<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Swagger;

use Doctrine\Common\Annotations\AnnotationReader;
use Nelmio\ApiDocBundle\Annotation\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\ModelRegister;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use PHPUnit\Framework\TestCase;

class ModelRegisterTest extends TestCase
{
    /**
     * @group legacy
     */
    public function testDeprecatedImplicitUseOfModel()
    {
        $api = new OA\OpenApi([]);
        $registry = new ModelRegistry([new NullModelDescriber()], $api);
        $modelRegister = new ModelRegister($registry, ['json']);

        $annotationsReader = new AnnotationReader();

        $modelRegister->__invoke(new Analysis([$annotation = $annotationsReader->getPropertyAnnotation(
            new \ReflectionProperty(Foo::class, 'bar'),
            OA\Property::class
        )]));

        $this->assertEquals([], json_decode(json_encode($annotation), true));
    }
}

class Foo
{
    /**
     * @OA\Property(@ModelAnnotation(type=Foo::class))
     */
    private $bar;
}

class NullModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, OA\Schema $schema)
    {
    }

    public function supports(Model $model): bool
    {
        return true;
    }
}
