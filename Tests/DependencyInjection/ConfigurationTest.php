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

use Nelmio\ApiDocBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultArea()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['areas' => ['path_patterns' => ['/foo']]]]);

        $this->assertSame(
            [
                'default' => [
                    'path_patterns' => ['/foo'],
                    'host_patterns' => [],
                    'name_patterns' => [],
                    'with_annotation' => false,
                    'documentation' => [],
                ],
            ],
            $config['areas']
        );
    }

    public function testAreas()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [['areas' => $areas = [
            'default' => [
                'path_patterns' => ['/foo'],
                'host_patterns' => [],
                'with_annotation' => false,
                'documentation' => [],
                'name_patterns' => [],
            ],
            'internal' => [
                'path_patterns' => ['/internal'],
                'host_patterns' => ['^swagger\.'],
                'with_annotation' => false,
                'documentation' => [],
                'name_patterns' => [],
            ],
            'commercial' => [
                'path_patterns' => ['/internal'],
                'host_patterns' => [],
                'with_annotation' => false,
                'documentation' => [],
                'name_patterns' => [],
            ],
        ]]]);

        $this->assertSame($areas, $config['areas']);
    }

    public function testAlternativeNames()
    {
        $processor = new Processor();
        $config = $processor->processConfiguration(new Configuration(), [[
            'models' => [
                'names' => [
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'groups' => ['group'],
                    ],
                    [
                        'alias' => 'Foo2',
                        'type' => 'App\Foo',
                        'groups' => [],
                    ],
                    [
                        'alias' => 'Foo3',
                        'type' => 'App\Foo',
                    ],
                    [
                        'alias' => 'Foo4',
                        'type' => 'App\Foo',
                        'groups' => ['group'],
                        'areas' => ['internal'],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'areas' => ['internal'],
                    ],
                    [
                        'alias' => 'Foo1',
                        'type' => 'App\Foo',
                        'groups' => ['group1', ['group2', 'parent' => 'child3']],
                    ],
                ],
            ],
        ]]);
        $this->assertEquals([
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => ['group'],
                'areas' => [],
            ],
            [
                'alias' => 'Foo2',
                'type' => 'App\Foo',
                'groups' => [],
                'areas' => [],
            ],
            [
                'alias' => 'Foo3',
                'type' => 'App\Foo',
                'groups' => null,
                'areas' => [],
            ],
            [
                'alias' => 'Foo4',
                'type' => 'App\\Foo',
                'groups' => ['group'],
                'areas' => ['internal'],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\\Foo',
                'groups' => null,
                'areas' => ['internal'],
            ],
            [
                'alias' => 'Foo1',
                'type' => 'App\Foo',
                'groups' => ['group1', ['group2', 'parent' => 'child3']],
                'areas' => [],
            ],
        ], $config['models']['names']);
    }
}
