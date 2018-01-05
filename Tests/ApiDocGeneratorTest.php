<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests;

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Nelmio\ApiDocBundle\Describer\DefaultDescriber;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class ApiDocGeneratorTest extends TestCase
{
    public function testCache()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter);

        $this->assertEquals($generator->generate(), $adapter->getItem('swagger_doc')->get());
    }

    public function testCacheWithCustomId()
    {
        $adapter = new ArrayAdapter();
        $generator = new ApiDocGenerator([new DefaultDescriber()], [], $adapter, 'custom_id');

        $this->assertEquals($generator->generate(), $adapter->getItem('custom_id')->get());
    }
}
