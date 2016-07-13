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

        $this->assertEquals(['https'], $operation->getSchemes()->toArray());
        $this->assertEmpty($operation->getSummary());
        $this->assertEmpty($operation->getDescription());
        $this->assertFalse($operation->getDeprecated());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->search('user', 'path'));

        $parameter = $parameters->find('user', 'path');
        $this->assertTrue($parameter->getRequired());
        $this->assertEquals('string', $parameter->getType());
        $this->assertEquals('/foo/', $parameter->getFormat());
    }

    public function testNelmioAction()
    {
        $operation = $this->getOperation('/nelmio', 'post');

        $this->assertEquals('This action is described.', $operation->getDescription());
        $this->assertFalse($operation->getDeprecated());
    }

    public function testDeprecatedAction()
    {
        $operation = $this->getOperation('/deprecated', 'get');

        $this->assertEquals('This action is deprecated.', $operation->getSummary());
        $this->assertEquals('Please do not use this action.', $operation->getDescription());
        $this->assertTrue($operation->getDeprecated());
    }

    private function getSwaggerDefinition()
    {
        static::createClient();

        return static::$kernel->getContainer()->get('exsyst_api_doc.generator')->extract();
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
