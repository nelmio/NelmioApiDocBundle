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
use EXSyst\Component\Swagger\Schema;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    public function testUndocumentedAction()
    {
        $paths = $this->getSwaggerDefinition()->getPaths();
        $this->assertFalse($paths->has('/undocumented'));
        $this->assertFalse($paths->has('/api/admin'));
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

        // Action
        $this->assertEquals('This action is described.', $operation->getDescription());
        $this->assertNull($operation->getDeprecated());

        // Parameters
        $parameters = $operation->getParameters();
        $foo = $parameters->get('foo', 'path');
        $this->assertTrue($foo->getRequired());
        $this->assertEquals('string', $foo->getType());

        // Input model
        $this->assertTrue($parameters->has('input', 'body'));
        $this->assertEquals('#/definitions/Dummy2', $parameters->get('input', 'body')->getSchema()->getRef());

        // Responses / Status codes
        $responses = $operation->getResponses();
        $this->assertTrue($responses->has('200'));
        $this->assertEquals('Returned when successful', $responses->get('200')->getDescription());

        $this->assertTrue($responses->has('403'));
        $this->assertEquals('Returned when the user is not authorized to say hello', $responses->get('403')->getDescription());

        // Output model
        $this->assertEquals('#/definitions/User', $responses->get('200')->getSchema()->getRef());
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

        $this->assertTrue($properties->has('users'));
        $this->assertEquals('#/definitions/User[]', $properties->get('users')->getRef());

        $this->assertTrue($properties->has('dummy'));
        $this->assertEquals('#/definitions/Dummy2', $properties->get('dummy')->getRef());
    }

    public function testUsersModel()
    {
        $model = $this->getModel('User[]');
        $this->assertEquals('array', $model->getType());

        $this->assertEquals('#/definitions/User', $model->getItems()->getRef());
    }

    private function getSwaggerDefinition()
    {
        static::createClient();

        return static::$kernel->getContainer()->get('nelmio_api_doc.generator')->generate();
    }

    private function getModel($name): Schema
    {
        $definitions = $this->getSwaggerDefinition()->getDefinitions();
        $this->assertTrue($definitions->has($name));

        return $definitions->get($name);
    }

    private function getOperation($path, $method): Operation
    {
        $api = $this->getSwaggerDefinition();
        $paths = $api->getPaths();

        $this->assertTrue($paths->has($path), sprintf('Path "%s" does not exist.', $path));
        $action = $paths->get($path);

        $this->assertTrue($action->hasOperation($method));

        return $action->getOperation($method);
    }
}
