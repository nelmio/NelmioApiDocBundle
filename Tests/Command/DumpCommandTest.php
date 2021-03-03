<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Command;

use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase; // for the creation of the kernel
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DumpCommandTest extends WebTestCase
{
    public function testExecute()
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('nelmio:apidoc:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            '--area' => 'test',
            '--no-pretty' => '',
        ]);

        // the output of the command in the console
        $output = $commandTester->getDisplay();
        $this->assertEquals(json_encode($this->getOpenApiDefinition('test'))."\n", $output);
    }
}
