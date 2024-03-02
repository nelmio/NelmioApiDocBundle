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

use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
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
            'pretty print' => [[], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES],
            'one line' => [['--no-pretty'], 0 | JSON_UNESCAPED_SLASHES],
        ];
    }

    public function testYaml()
    {
        $output = $this->executeDumpCommand([
            '--format' => 'yaml',
            '--server-url' => 'http://example.com/api',
        ]);
        $expectedYaml = <<<YAML
servers:
  -
    url: 'http://example.com/api'
YAML;
        self::assertStringContainsString($expectedYaml, $output);
    }

    /** @dataProvider provideAssetsMode */
    public function testHtml($htmlConfig, string $expectedHtml)
    {
        $output = $this->executeDumpCommand([
            '--area' => 'test',
            '--format' => 'html',
            '--html-config' => json_encode($htmlConfig),
        ]);
        self::assertStringContainsString('<body>', $output);
        self::assertStringContainsString($expectedHtml, $output);
    }

    public function provideAssetsMode()
    {
        return [
            'default mode is cdn' => [
                null,
                'https://cdn.jsdelivr.net',
            ],
            'invalid mode fallbacks to cdn' => [
                'invalid',
                'https://cdn.jsdelivr.net',
            ],
            'select cdn mode' => [
                ['assets_mode' => AssetsMode::CDN],
                'https://cdn.jsdelivr.net',
            ],
            'select offline mode' => [
                ['assets_mode' => AssetsMode::OFFLINE],
                '<style>',
            ],
            'configure swagger ui' => [
                [
                    'swagger_ui_config' => [
                        'supportedSubmitMethods' => ['get'],
                    ],
                ],
                '"supportedSubmitMethods":["get"]',
            ],
            'configure server url' => [
                [
                    'server_url' => 'http://example.com/api',
                ],
                '[{"url":"http://example.com/api"}]',
            ],
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
