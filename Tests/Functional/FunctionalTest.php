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

use EXSyst\Component\Swagger\Operation;
use EXSyst\Component\Swagger\Tag;

class FunctionalTest extends WebTestCase
{
    public function testConfiguredDocumentation()
    {
        $this->assertEquals('My Test App', $this->getSwaggerDefinition()->getInfo()->getTitle());
    }

    public function testUndocumentedAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();
        $this->assertFalse($paths->has('/undocumented'));
        $this->assertFalse($paths->has('/api/admin'));
    }

    public function testFetchArticleAction()
    {
        $operation = $this->getOperation('/api/article/{id}', 'get');

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('200'));
        $this->assertEquals('#/definitions/Article', $responses->get('200')->getSchema()->getRef());

        // Ensure that groups are supported
        $modelProperties = $this->getModel('Article')->getProperties();
        $this->assertCount(1, $modelProperties);
        $this->assertTrue($modelProperties->has('author'));
        $this->assertFalse($modelProperties->has('content'));
    }

    public function testFilteredAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();

        $this->assertFalse($paths->has('/filtered'));
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

        $this->assertEquals(array(new Tag('implicit')), $operation->getTags());

        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('201'));
        $response = $responses->get('201');
        $this->assertEquals('Operation automatically detected', $response->getDescription());
        $this->assertEquals('#/definitions/User', $response->getSchema()->getRef());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('foo', 'body'));
        $parameter = $parameters->get('foo', 'body');

        $this->assertEquals('This is a parameter', $parameter->getDescription());
        $this->assertEquals('#/definitions/User', $parameter->getSchema()->getItems()->getRef());
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
        $this->assertTrue($operation->getResponses()->has(200));

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

    public function testUserModel()
    {
        $model = $this->getModel('User');
        $this->assertEquals('object', $model->getType());
        $properties = $model->getProperties();
        $this->assertCount(3, $properties);

        $this->assertTrue($properties->has('users'));
        $this->assertEquals('#/definitions/User[]', $properties->get('users')->getRef());

        $this->assertTrue($properties->has('dummy'));
        $this->assertEquals('#/definitions/Dummy2', $properties->get('dummy')->getRef());

        $this->assertTrue($properties->has('createdAt'));
        $this->assertEquals('#/definitions/DateTime', $properties->get('createdAt')->getRef());

        $model = $this->getModel('DateTime');
        $this->assertEquals('string', $model->getType());
        $this->assertEquals('date-time', $model->getFormat());
    }

    public function testUsersModel()
    {
        $model = $this->getModel('User[]');
        $this->assertEquals('array', $model->getType());

        $this->assertEquals('#/definitions/User', $model->getItems()->getRef());
    }

    public function testFormSupport()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'bar' => [
                    'type' => 'string',
                ],
                'foo' => [
                    'type' => 'string',
                    'enum' => ['male', 'female'],
                ],
            ],
            'required' => ['foo'],
        ], $this->getModel('DummyType')->toArray());
    }
}
