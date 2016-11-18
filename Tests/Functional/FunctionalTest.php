<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FunctionalTest extends WebTestCase
{
    public function testUserAction()
    {
        $operation = $this->getOperation('/test/{user}', 'get');

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

    public function testNelmioAction()
    {
        $operation = $this->getOperation('/nelmio/{foo}', 'post');

        $this->assertEquals('This action is described.', $operation->getDescription());
        $this->assertNull($operation->getDeprecated());

        $foo = $operation->getParameters()->get('foo', 'path');
        $this->assertTrue($foo->getRequired());
        $this->assertEquals('string', $foo->getType());
    }

    public function testDeprecatedAction()
    {
        $operation = $this->getOperation('/deprecated', 'get');

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

        return static::$kernel->getContainer()->get('exsyst_api_doc.generator')->generate();
    }

    private function getOperation($path, $method)
    {
        $api = $this->getSwaggerDefinition();
        $paths = $api->getPaths();

        $this->assertTrue($paths->has($path));
        $action = $paths->get($path);

        $this->assertTrue($action->hasOperation($method));

        return $action->getOperation($method);
    }
}
