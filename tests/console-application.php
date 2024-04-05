<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\ApiDocBundle\Tests\Functional\TestBundle;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\HttpKernel\Kernel;

require __DIR__.'/../vendor/autoload.php';

$kernel = new class('phpstan', false) extends Kernel {
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new NelmioApiDocBundle(),
            new TestBundle(),
        ];
    }
};

return new Application($kernel);
