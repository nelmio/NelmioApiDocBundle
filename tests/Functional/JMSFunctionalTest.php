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

use Symfony\Component\HttpKernel\KernelInterface;

class JMSFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testModelPictureDocumentation(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
            ],
            'schema' => 'JMSPicture',
        ], json_decode($this->getModel('JMSPicture')->toJson(), true));

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'only_direct_picture_mini' => [
                    'type' => 'integer',
                ],
            ],
            'schema' => 'JMSPicture_mini',
        ], json_decode($this->getModel('JMSPicture_mini')->toJson(), true));
    }

    public function testModeChatDocumentation(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'members' => [
                    'items' => [
                        '$ref' => '#/components/schemas/JMSChatUser',
                    ],
                    'type' => 'array',
                ],
            ],
            'schema' => 'JMSChat',
        ], json_decode($this->getModel('JMSChat')->toJson(), true));

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'picture' => [
                    '$ref' => '#/components/schemas/JMSPicture',
                ],
            ],
            'schema' => 'JMSChatUser',
        ], json_decode($this->getModel('JMSChatUser')->toJson(), true));
    }

    public function testModelDocumentation(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'User id',
                    'readOnly' => true,
                    'title' => 'userid',
                    'example' => 1,
                    'default' => null,
                ],
                'daysOnline' => [
                    'type' => 'integer',
                    'default' => 0,
                    'minimum' => 1,
                    'maximum' => 300,
                ],
                'email' => [
                    'type' => 'string',
                    'readOnly' => false,
                ],
                'roles' => [
                    'type' => 'array',
                    'title' => 'roles',
                    'example' => '["ADMIN","SUPERUSER"]',
                    'items' => ['type' => 'string'],
                    'default' => ['user'],
                    'description' => 'Roles list',
                ],
                'location' => [
                    'type' => 'string',
                    'title' => 'User Location.',
                ],
                'friendsNumber' => [
                    'type' => 'string',
                    'maxLength' => 100,
                    'minLength' => 1,
                ],
                'friends' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/components/schemas/User',
                    ],
                ],
                'indexed_friends' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        '$ref' => '#/components/schemas/User',
                    ],
                ],
                'favorite_dates' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'string',
                        'format' => 'date-time',
                    ],
                ],
                'custom_date' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
                'best_friend' => [
                    '$ref' => '#/components/schemas/User',
                ],
                'status' => [
                    'type' => 'string',
                    'title' => 'Whether this user is enabled or disabled.',
                    'description' => 'Only enabled users may be used in actions.',
                    'enum' => ['disabled', 'enabled'],
                ],
                'virtual_type1' => [
                    'title' => 'JMS custom types handled via Custom Type Handlers.',
                    'oneOf' => [['$ref' => '#/components/schemas/VirtualTypeClassDoesNotExistsHandlerDefined']],
                ],
                'virtual_type2' => [
                    'title' => 'JMS custom types handled via Custom Type Handlers.',
                    'oneOf' => [['$ref' => '#/components/schemas/VirtualTypeClassDoesNotExistsHandlerNotDefined']],
                ],
                'last_update' => [
                    'type' => 'date',
                ],
                'lat_lon_history' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'number',
                            'format' => 'float',
                        ],
                    ],
                ],
                'free_form_object_without_type' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                ],
                'free_form_object' => [
                    'type' => 'object',
                    'additionalProperties' => true,
                ],
                'deep_object' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'string',
                            'format' => 'date-time',
                        ],
                    ],
                ],
                'deep_object_with_items' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'string',
                            'format' => 'date-time',
                        ],
                    ],
                ],
                'deep_free_form_object_collection' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            'additionalProperties' => true,
                        ],
                    ],
                ],
                'long' => [
                    'type' => 'string',
                ],
                'short' => [
                    'type' => 'integer',
                ],
            ],
            'schema' => 'JMSUser',
        ], json_decode($this->getModel('JMSUser')->toJson(), true));

        self::assertEquals([
            'schema' => 'VirtualTypeClassDoesNotExistsHandlerNotDefined',
        ], json_decode($this->getModel('VirtualTypeClassDoesNotExistsHandlerNotDefined')->toJson(), true));

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'custom_prop' => [
                    'type' => 'string',
                ],
            ],
            'schema' => 'VirtualTypeClassDoesNotExistsHandlerDefined',
        ], json_decode($this->getModel('VirtualTypeClassDoesNotExistsHandlerDefined')->toJson(), true));
    }

    public function testModelComplexDualDocumentation(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'complex' => [
                    '$ref' => '#/components/schemas/JMSComplexDefault',
                ],
                'user' => [
                    '$ref' => '#/components/schemas/JMSUser',
                ],
            ],
            'schema' => 'JMSDualComplex',
        ], json_decode($this->getModel('JMSDualComplex')->toJson(), true));
    }

    public function testNestedGroups(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'living' => ['$ref' => '#/components/schemas/JMSChatLivingRoom'],
                'dining' => ['$ref' => '#/components/schemas/JMSChatRoom'],
            ],
            'schema' => 'JMSChatFriend',
        ], json_decode($this->getModel('JMSChatFriend')->toJson(), true));

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id1' => ['type' => 'integer'],
                'id3' => ['type' => 'integer'],
            ],
            'schema' => 'JMSChatRoom',
        ], json_decode($this->getModel('JMSChatRoom')->toJson(), true));
    }

    public function testModelComplexDocumentation(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'user' => ['$ref' => '#/components/schemas/JMSUser'],
                'name' => ['type' => 'string'],
                'virtual' => ['$ref' => '#/components/schemas/JMSUser'],
                'virtual_friend' => ['$ref' => '#/components/schemas/JMSUser'],
            ],
            'required' => [
                'id',
                'user',
            ],
            'schema' => 'JMSComplex',
        ], json_decode($this->getModel('JMSComplex')->toJson(), true));
    }

    public function testYamlConfig(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'email' => [
                    'type' => 'string',
                ],
                'virtualprop' => [
                    'type' => 'string',
                ],
            ],
            'schema' => 'VirtualProperty',
        ], json_decode($this->getModel('VirtualProperty')->toJson(), true));
    }

    public function testNoCollisionsAreGenerated(): void
    {
        self::assertFalse($this->hasModel('JMSComplex2'));
        self::assertFalse($this->hasModel('JMSUser2'));
        self::assertFalse($this->hasModel('JMSChatRoom2'));
        self::assertFalse($this->hasModel('JMSChatRoomUser2'));
        self::assertFalse($this->hasModel('JMSChatLivingRoom2'));

        self::assertFalse($this->hasModel('JMSPicture2'));
    }

    public function testNamingStrategyWithConstraints(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'beautifulName' => [
                    'type' => 'string',
                    'maxLength' => 10,
                    'minLength' => 3,
                ],
            ],
            'required' => ['beautifulName'],
            'schema' => 'JMSNamingStrategyConstraints',
        ], json_decode($this->getModel('JMSNamingStrategyConstraints')->toJson(), true));
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testEnumSupport(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'type' => [
                    '$ref' => '#/components/schemas/ArticleType81',
                ],
                'int_backed_type' => [
                    '$ref' => '#/components/schemas/ArticleType81IntBacked',
                ],
                'not_backed_type' => [
                    '$ref' => '#/components/schemas/ArticleType81NotBacked',
                ],
                'nullable_type' => [
                    '$ref' => '#/components/schemas/ArticleType81',
                ],
            ],
            'schema' => 'Article81',
        ], json_decode($this->getModel('Article81')->toJson(), true));

        self::assertEquals([
            'schema' => 'ArticleType81',
            'type' => 'string',
            'enum' => [
                'draft',
                'final',
            ],
        ], json_decode($this->getModel('ArticleType81')->toJson(), true));
    }

    public function testModeDiscriminatorMap(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                ],
            ],
            'schema' => 'JMSManager',
        ], json_decode($this->getModel('JMSManager')->toJson(), true));

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'username' => [
                    'type' => 'string',
                ],
                'admin_title' => [
                    'type' => 'string',
                ],
            ],
            'schema' => 'JMSAdministrator',
        ], json_decode($this->getModel('JMSAdministrator')->toJson(), true));

        self::assertEquals([
            'oneOf' => [
                ['$ref' => '#/components/schemas/JMSManager'],
                ['$ref' => '#/components/schemas/JMSAdministrator'],
            ],
            'schema' => 'JMSAbstractUser',
            'discriminator' => [
                'propertyName' => 'type',
                'mapping' => [
                    'manager' => '#/components/schemas/JMSManager',
                    'administrator' => '#/components/schemas/JMSAdministrator',
                ],
            ],
        ], json_decode($this->getModel('JMSAbstractUser')->toJson(), true));
    }

    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_JMS);
    }
}
