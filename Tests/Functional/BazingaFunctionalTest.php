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
use OpenApi\Generator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class BazingaFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        if (Kernel::MAJOR_VERSION >= 7) {
            $this->markTestSkipped('Not supported in symfony 7');
        }

        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testModelComplexDocumentationBazinga()
    {
        $model = $this->getModel('BazingaUser');

        if (Generator::UNDEFINED === $model->properties) {
            $this->markTestSkipped('Typed embedded properties require at least willdurand/hateoas 3.0');
        }

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
            'schema' => 'BazingaUser',
        ], json_decode($model->toJson(), true));
    }

    public function testWithGroup()
    {
        $model = $this->getModel('BazingaUser_grouped');

        if (Generator::UNDEFINED === $model->properties) {
            $this->markTestSkipped('Typed embedded properties require at least willdurand/hateoas 3.0');
        }

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
            'schema' => 'BazingaUser_grouped',
        ], json_decode($model->toJson(), true));
    }

    public function testWithType()
    {
        try {
            new \ReflectionMethod(Embedded::class, 'getType');
        } catch (\ReflectionException $e) {
            $this->markTestSkipped('Typed embedded properties require at least willdurand/hateoas 3.0');
        }

        $model = $this->getModel('BazingaUserTyped');

        if (Generator::UNDEFINED === $model->properties) {
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
                                '$ref' => '#/components/schemas/BazingaUser',
                            ],
                            'type' => 'array',
                        ],
                        'typed_bazinga_name' => [
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
            'schema' => 'BazingaUserTyped',
        ], json_decode($model->toJson(), true));
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_JMS | TestKernel::USE_BAZINGA);
    }
}
