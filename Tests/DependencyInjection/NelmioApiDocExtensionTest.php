<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\DependencyInjection;

use Nelmio\ApiDocBundle\DependencyInjection\NelmioApiDocExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NelmioApiDocExtensionTest extends TestCase
{
    public function testNameAliasesArePassedToModelRegistry()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $extension = new NelmioApiDocExtension();
        $extension->load([[
            'areas' => [
                'default' => ['path_patterns' => ['/foo'], 'host_patterns' => []],
                'commercial' => ['path_patterns' => ['/internal'], 'host_patterns' => []],
            ],
            'models' => [
                'names' => [
                    'Foo1' => [
                        'type' => 'App\Foo',
                    ],
                    'Bar1' => [
                        'type' => 'App\Bar',
                        'areas' => ['commercial'],
                    ],
                ]
            ]
        ]], $container);

        $methodCalls = $container->getDefinition('nelmio_api_doc.generator.default')->getMethodCalls();
        $foundMethodCall = false;
        foreach ($methodCalls as $methodCall) {
            if ('setAlternativeNames' == $methodCall[0]){
                $this->assertEquals([
                    'Foo1' => [
                        'type' => 'App\\Foo',
                        'groups' => [],
                    ],
                ], $methodCall[1][0]);
                $foundMethodCall = true;
            }
        }
        $this->assertTrue($foundMethodCall);

        $methodCalls = $container->getDefinition('nelmio_api_doc.generator.commercial')->getMethodCalls();
        $foundMethodCall = false;
        foreach ($methodCalls as $methodCall) {
            if ('setAlternativeNames' == $methodCall[0]){
                $this->assertEquals([
                    'Bar1' => [
                        'type' => 'App\\Bar',
                        'groups' => [],
                    ],
                ], $methodCall[1][0]);
                $foundMethodCall = true;
            }
        }
        $this->assertTrue($foundMethodCall);
    }
    public function testMergesRootKeysFromMultipleConfigurations()
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.bundles', []);
        $extension = new NelmioApiDocExtension();
        $extension->load([
            [
                'documentation' => [
                    'info' => [
                        'title' => 'API documentation',
                        'description' => 'This is the api documentation, use it wisely',
                    ],
                ],
            ],
            [
                'documentation' => [
                    'tags' => [
                        [
                            'name' => 'secured',
                            'description' => 'Requires authentication',
                        ],
                        [
                            'name' => 'another',
                            'description' => 'Another tag serving another purpose',
                        ],
                    ],
                ],
            ],
            [
                'documentation' => [
                    'paths' => [
                        '/api/v1/model' => [
                            'get' => [
                                'tags' => ['secured'],
                            ],
                        ],
                    ],
                ],
            ],
        ], $container);

        $this->assertSame([
            'info' => [
                'title' => 'API documentation',
                'description' => 'This is the api documentation, use it wisely',
            ],
            'tags' => [
                [
                    'name' => 'secured',
                    'description' => 'Requires authentication',
                ],
                [
                    'name' => 'another',
                    'description' => 'Another tag serving another purpose',
                ],
            ],
            'paths' => [
                '/api/v1/model' => [
                    'get' => [
                        'tags' => ['secured'],
                    ],
                ],
            ],
        ], $container->getDefinition('nelmio_api_doc.describers.config')->getArgument(0));
    }
}
