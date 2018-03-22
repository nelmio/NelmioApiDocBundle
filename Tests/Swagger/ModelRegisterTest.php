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
use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Annotation\Model as ModelAnnotation;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\SwaggerPhp\ModelRegister;
use PHPUnit\Framework\TestCase;
use  Swagger\Analysis;
use Swagger\Annotations as SWG;

class ModelRegisterTest extends TestCase
{
    /**
     * @group legacy
     * @expectedDeprecation Using `@Model` implicitely in a `@SWG\Schema`, `@SWG\Items` or `@SWG\Property` annotation in %s. Use `ref=@Model()` instead.
     */
    public function testDeprecatedImplicitUseOfModel()
    {
        $api = new Swagger();
        $registry = new ModelRegistry([new NullModelDescriber()], $api);
        $modelRegister = new ModelRegister($registry);

        $annotationsReader = new AnnotationReader();

        $modelRegister->__invoke(new Analysis([$annotation = $annotationsReader->getPropertyAnnotation(
            new \ReflectionProperty(Foo::class, 'bar'),
            SWG\Property::class
        )]));

        $this->assertEquals(['items' => ['$ref' => '#/definitions/Foo']], json_decode(json_encode($annotation), true));
    }
}

class Foo
{
    /**
     * @SWG\Property(@ModelAnnotation(type=Foo::class))
     */
    private $bar;
}

class NullModelDescriber implements ModelDescriberInterface
{
    public function describe(Model $model, Schema $schema)
    {
    }

    public function supports(Model $model): bool
    {
        return true;
    }
}
