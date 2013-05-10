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
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\DependencyInjection\Scope;

abstract class WebTestCase extends BaseWebTestCase
{
    protected function deleteTmpDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/'.Kernel::VERSION)) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
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

        return static::$kernel->getContainer();
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

    public function setUp()
    {
        parent::setUp();
        $this->deleteTmpDir();
    }
}
