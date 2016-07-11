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
    public function testUserActionApiController()
    {
        $api = $this->getSwaggerDefinition();
        $paths = $api->getPaths();

        $this->assertTrue($paths->has('/test/{user}'));
        $action = $paths->get('/test/{user}');

        $this->assertTrue($action->hasOperation('get'));
        $operation = $action->getOperation('get');

        $this->assertEquals(['https'], $operation->getSchemes()->toArray());

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->search('user', 'path'));

        $parameter = $parameters->find('user', 'path');
        $this->assertTrue($parameter->getRequired());
        $this->assertEquals('string', $parameter->getType());
        $this->assertEquals('/foo/', $parameter->getFormat());
    }

    private function getSwaggerDefinition()
    {
        static::createClient();

        return static::$kernel->getContainer()->get('exsyst_api_doc.generator')->extract();
    }
}
