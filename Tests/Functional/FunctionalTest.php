<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    public function testUndocumentedAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();
        $this->assertFalse($paths->has('/undocumented'));
        $this->assertFalse($paths->has('/api/admin'));
    }

    /**
     * Tests that the paths are automatically resolved in Swagger annotations.
     *
     * @dataProvider swaggerActionPathsProvider
     */
    public function testSwaggerAction($path)
    {
        $operation = $this->getOperation($path, 'get');

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('201'));
        $this->assertEquals('An example resource', $responses->get('201')->getDescription());
    }

    public function swaggerActionPathsProvider()
    {
        return [['/api/swagger'], ['/api/swagger2']];
    }

    /**
     * @dataProvider implicitSwaggerActionMethodsProvider
     */
    public function testImplicitSwaggerAction($method)
    {
        $operation = $this->getOperation('/api/swagger/implicit', $method);

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('201'));
        $this->assertEquals('Operation automatically detected', $responses->get('201')->getDescription());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('foo', 'query'));
        $this->assertEquals('This is a parameter', $parameters->get('foo', 'query')->getDescription());
    }

    public function implicitSwaggerActionMethodsProvider()
    {
        return [['get'], ['post']];
    }

    public function testUserAction()
    {
        $operation = $this->getOperation('/api/test/{user}', 'get');

        $this->assertEquals(['https'], $operation->getSchemes());
        $this->assertEmpty($operation->getSummary());
        $this->assertEmpty($operation->getDescription());
        $this->assertNull($operation->getDeprecated());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('user', 'path'));

        $parameter = $parameters->get('user', 'path');
        $this->assertTrue($parameter->getRequired());
        $this->assertEquals('string', $parameter->getType());
        $this->assertEquals('/foo/', $parameter->getFormat());
    }

    public function testFOSRestAction()
    {
        $operation = $this->getOperation('/api/fosrest', 'post');

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('foo', 'query'));
        $this->assertTrue($parameters->has('bar', 'formData'));

        // The _format path attribute should be removed
        $this->assertFalse($parameters->has('_format', 'path'));
    }

    public function testNelmioAction()
    {
        $operation = $this->getOperation('/api/nelmio/{foo}', 'post');

        $this->assertEquals('This action is described.', $operation->getDescription());
        $this->assertNull($operation->getDeprecated());

        $foo = $operation->getParameters()->get('foo', 'path');
        $this->assertTrue($foo->getRequired());
        $this->assertEquals('string', $foo->getType());
    }

    public function testDeprecatedAction()
    {
        $operation = $this->getOperation('/api/deprecated', 'get');

        $this->assertEquals('This action is deprecated.', $operation->getSummary());
        $this->assertEquals('Please do not use this action.', $operation->getDescription());
        $this->assertTrue($operation->getDeprecated());
    }

    public function testApiPlatform()
    {
        $operation = $this->getOperation('/api/dummies', 'get');
        $operation = $this->getOperation('/api/foo', 'get');
        $operation = $this->getOperation('/api/foo', 'post');
        $operation = $this->getOperation('/api/dummies/{id}', 'get');
    }

    private function getSwaggerDefinition()
    {
        static::createClient();

        return static::$kernel->getContainer()->get('nelmio_api_doc.generator')->generate();
    }

    private function getOperation($path, $method)
    {
        $api = $this->getSwaggerDefinition();
        $paths = $api->getPaths();

        $this->assertTrue($paths->has($path), sprintf('Path "%s" does not exist', $path));
        $action = $paths->get($path);

        $this->assertTrue($action->hasOperation($method), sprintf('Operation "%s" for path "%s" does not exist', $path, $method));

        return $action->getOperation($method);
    }
}
