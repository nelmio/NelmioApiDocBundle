<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Hateoas\Configuration\Embedded;

class BazingaFunctionalTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testModelComplexDocumentationBazinga()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                '_links' => [
                    'readOnly' => true,
                    'properties' => [
                        'example' => [
                            'properties' => [
                                'href' => [
                                    'type' => 'string',
                                ],
                                'str_att' => [
                                    'type' => 'string',
                                    'default' => 'bar',
                                ],
                                'float_att' => [
                                    'type' => 'number',
                                    'default' => 5.6,
                                ],
                                'bool_att' => [
                                    'type' => 'boolean',
                                    'default' => false,
                                ],
                            ],
                            'type' => 'object',
                        ],
                        'route' => [
                            'properties' => [
                                'href' => [
                                    'type' => 'string',
                                ],
                            ],
                            'type' => 'object',
                        ],
                    ],
                ],
                '_embedded' => [
                    'readOnly' => true,
                    'properties' => [
                        'route' => [
                            'type' => 'object',
                        ],
                        'embed_with_group' => [
                            'type' => 'object',
                        ],
                    ],
                ],
            ],
        ], $this->getModel('BazingaUser')->toArray());
    }

    public function testWithGroup()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                '_embedded' => [
                    'readOnly' => true,
                    'properties' => [
                        'embed_with_group' => [
                            'type' => 'object',
                        ],
                    ],
                ],
            ],
        ], $this->getModel('BazingaUser_grouped')->toArray());
    }

    public function testWithType()
    {
        try {
            new \ReflectionMethod(Embedded::class, 'getType');
        } catch (\ReflectionException $e) {
            $this->markTestSkipped('Typed embedded properties require at least willdurand/hateoas 3.0');
        }
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                '_embedded' => [
                    'readOnly' => true,
                    'properties' => [
                        'typed_bazinga_users' => [
                            'items' => [
                                '$ref' => '#/definitions/BazingaUser',
                            ],
                            'type' => 'array',
                        ],
                        'typed_bazinga_name' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ], $this->getModel('BazingaUserTyped')->toArray());
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel(true, true);
    }
}
