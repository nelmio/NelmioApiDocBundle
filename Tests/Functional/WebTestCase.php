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
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected static function createKernel(array $options = array())
    {
        return new TestKernel();
    }

    protected function getSwaggerDefinition()
    {
        static::createClient();

        return static::$kernel->getContainer()->get('nelmio_api_doc.generator')->generate();
    }

    protected function getModel($name): Schema
    {
        $definitions = $this->getSwaggerDefinition()->getDefinitions();
        $this->assertTrue($definitions->has($name));

        return $definitions->get($name);
    }

    protected function getOperation($path, $method): Operation
    {
        $api = $this->getSwaggerDefinition();
        $paths = $api->getPaths();

        $this->assertTrue($paths->has($path), sprintf('Path "%s" does not exist.', $path));
        $action = $paths->get($path);

        $this->assertTrue($action->hasOperation($method), sprintf('Operation "%s" for path "%s" does not exist', $path, $method));

        return $action->getOperation($method);
    }
}
