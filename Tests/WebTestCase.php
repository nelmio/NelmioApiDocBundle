<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\HttpKernel\Kernel;

abstract class WebTestCase extends BaseWebTestCase
{
    public static $container;

    protected function setUp()
    {
        parent::setUp();

        if (version_compare(Kernel::VERSION, '2.2.0', '<')) {
            $this->markTestSkipped('Does not work with Symfony2 2.1 due to a "host" parameter in the `routing.yml` file');
        }
    }

    public static function handleDeprecation($errorNumber, $message, $file, $line, $context)
    {
        if ($errorNumber & E_USER_DEPRECATED) {
            return true;
        }

        return \PHPUnit_Util_ErrorHandler::handleError($errorNumber, $message, $file, $line);
    }

    protected function getContainer(array $options = array())
    {
        if (!static::$kernel) {
            static::$kernel = static::createKernel($options);
        }

        static::$kernel->boot();

        if (!static::$container) {
            static::$container = static::$kernel->getContainer();
        }

        static::$container->set('kernel', static::$kernel);

        return static::$container;
    }

    protected static function getKernelClass()
    {
        require_once __DIR__.'/Fixtures/app/AppKernel.php';

        return 'Nelmio\ApiDocBundle\Tests\Functional\AppKernel';
    }

    protected static function createKernel(array $options = array())
    {
        $class = self::getKernelClass();

        return new $class(
            'default',
            isset($options['debug']) ? $options['debug'] : true
        );
    }
}
