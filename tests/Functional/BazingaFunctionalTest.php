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

use Metadata\Cache\PsrCacheAdapter;
use Metadata\MetadataFactory;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpKernel\KernelInterface;

#[Group('hateoas')]
class BazingaFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);

        $metaDataFactory = self::getContainer()->get('hateoas.configuration.metadata_factory');

        if (!$metaDataFactory instanceof MetadataFactory) {
            self::fail('The hateoas.metadata_factory service is not an instance of MetadataFactory');
        }

        // Reusing the cache from previous tests causes relations metadata to be lost, so we need to clear it
        $metaDataFactory->setCache(new PsrCacheAdapter('BazingaFunctionalTest', new ArrayAdapter()));
    }

    public function testModelComplexDocumentationBazinga(): void
    {
        self::assertEquals([
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
        ], json_decode($this->getModel('BazingaUser')->toJson(), true));
    }

    public function testWithGroup(): void
    {
        self::assertEquals([
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
        ], json_decode($this->getModel('BazingaUser_grouped')->toJson(), true));
    }

    public function testWithType(): void
    {
        self::assertEquals([
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
        ], json_decode($this->getModel('BazingaUserTyped')->toJson(), true));
    }

    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_BAZINGA);
    }
}
