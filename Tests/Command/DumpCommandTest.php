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
    /** @dataProvider provideJsonMode */
    public function testJson(array $jsonOptions, int $expectedJsonFlags)
    {
        $output = $this->executeDumpCommand($jsonOptions + [
            '--area' => 'test',
        ]);
        $this->assertEquals(
            json_encode($this->getOpenApiDefinition('test'), $expectedJsonFlags)."\n",
            $output
        );
    }

    public function provideJsonMode()
    {
        return [
            'pretty print' => [[], JSON_PRETTY_PRINT],
            'one line' => [['--no-pretty'], 0],
        ];
    }

    private function executeDumpCommand(array $options)
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('nelmio:apidoc:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute($options);

        return $commandTester->getDisplay();
    }
}
