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
use Nelmio\ApiDocBundle\Render\Html\Renderer;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase; // for the creation of the kernel
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DumpCommandTest extends WebTestCase
{
    /**
     * @param array<string, mixed> $jsonOptions
     */
    #[DataProvider('provideJsonMode')]
    public function testJson(array $jsonOptions, int $expectedJsonFlags): void
    {
        $output = $this->executeDumpCommand($jsonOptions + [
            '--area' => 'test',
        ]);
        self::assertEquals(
            json_encode($this->getOpenApiDefinition('test'), $expectedJsonFlags)."\n",
            $output
        );
    }

    public static function provideJsonMode(): \Generator
    {
        yield 'pretty print' => [[], \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES];

        yield 'one line' => [['--no-pretty'], 0 | \JSON_UNESCAPED_SLASHES];
    }

    public function testYaml(): void
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

    /**
     * @param mixed $htmlConfig the value of the --html-config option
     */
    #[DataProvider('provideAssetsMode')]
    public function testHtml($htmlConfig, string $expectedHtml): void
    {
        $output = $this->executeDumpCommand([
            '--area' => 'test',
            '--format' => 'html',
            '--html-config' => json_encode($htmlConfig),
        ]);
        self::assertStringContainsString('<html>', $output);
        self::assertStringContainsString('<body', $output);
        self::assertStringContainsString('</body>', $output);
        self::assertStringContainsString($expectedHtml, $output);
    }

    public static function provideAssetsMode(): \Generator
    {
        yield 'default mode is cdn' => [
            null,
            'https://cdn.jsdelivr.net',
        ];

        yield 'invalid mode fallbacks to cdn' => [
            'invalid',
            'https://cdn.jsdelivr.net',
        ];

        yield 'select cdn mode' => [
            ['assets_mode' => AssetsMode::CDN],
            'https://cdn.jsdelivr.net',
        ];

        yield 'select offline mode' => [
            ['assets_mode' => AssetsMode::OFFLINE],
            '<style>',
        ];

        yield 'configure swagger ui' => [
            [
                'swagger_ui_config' => [
                    'supportedSubmitMethods' => ['get'],
                ],
            ],
            '"supportedSubmitMethods":["get"]',
        ];

        yield 'configure redocly' => [
            [
                'ui_renderer' => Renderer::REDOCLY,
                'redocly_config' => [
                    'hideDownloadButton' => true,
                ],
            ],
            '"hideDownloadButton":true',
        ];

        yield 'configure stoplight' => [
            [
                'ui_renderer' => Renderer::STOPLIGHT,
            ],
            'stoplight/web-components.min.js',
        ];

        yield 'configure server url' => [
            [
                'server_url' => 'http://example.com/api',
            ],
            '[{"url":"http://example.com/api"}]',
        ];
    }

    /**
     * @param array<string, mixed> $options
     */
    private function executeDumpCommand(array $options): string
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find('nelmio:apidoc:dump');
        $commandTester = new CommandTester($command);
        $commandTester->execute($options);

        return $commandTester->getDisplay();
    }
}
