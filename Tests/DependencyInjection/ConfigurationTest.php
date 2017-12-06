<?php
namespace DependencyInjection;

use Nelmio\ApiDocBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testOnlyPathPatternsConfigurations()
    {
        $config = [
            'routes' => [
                'path_patterns' => ['/api/doc', '/api/info']
            ]
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $configuration,
            [$config]
        );

        $this->assertSame([
            'routes'        => [
                ['path_patterns' => ['/api/doc', '/api/info'], 'host' => null]
            ],
            'documentation' => [],
            'models'        => ['use_jms' => false]
        ], $processedConfiguration);
    }

    public function testMultipleRoutesConfigurations()
    {
        $config = [
            'routes' => [
                ['host' => null, 'path_patterns' => ['/api/doc', '/api/info']],
                ['host' => 'app.foobar.com', 'path_patterns' => ['/api/foo', '/api/bar']],
                ['host' => 'app.foobar.com', 'path_patterns' => ['/api/foo1', '/api/bar1']],
                ['host' => 'app.foobar.com', 'path_patterns' => ['/api/foo', '/api/bar1']],
            ],
        ];

        $processor = new Processor();
        $configuration = new Configuration();
        $processedConfiguration = $processor->processConfiguration(
            $configuration,
            [$config]
        );

        $this->assertSame([
            'routes'        => [
                ['host' => null, 'path_patterns' => ['/api/doc', '/api/info']],
                ['host' => 'app.foobar.com', 'path_patterns' => ['/api/foo', '/api/bar', '/api/foo1', '/api/bar1']],
            ],
            'documentation' => [],
            'models'        => ['use_jms' => false]
        ], $processedConfiguration);
    }
}