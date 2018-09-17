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

class JMSFunctionalTest extends WebTestCase
{
    public function testModelPictureDocumentation()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'only_direct_picture_mini' => [
                    'type' => 'integer',
                ],
            ],
        ], $this->getModel('JMSPicture')->toArray());
    }

    public function testModeChatDocumentation()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'members' => [
                    'items' => [
                        '$ref' => '#/definitions/JMSChatUser',
                    ],
                    'type' => 'array',
                ],
            ],
        ], $this->getModel('JMSChat')->toArray());

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'picture' => [
                    '$ref' => '#/definitions/JMSPicture',
                ],
            ],
        ], $this->getModel('JMSChatUser')->toArray());
    }

    public function testModelDocumentation()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'description' => 'User id',
                    'readOnly' => true,
                    'title' => 'userid',
                    'example' => 1,
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
                'friendsNumber' => [
                    'type' => 'string',
                    'maxLength' => 100,
                    'minLength' => 1,
                ],
                'friends' => [
                    'type' => 'array',
                    'items' => [
                        '$ref' => '#/definitions/User',
                    ],
                ],
                'indexed_friends' => [
                    'type' => 'object',
                    'additionalProperties' => [
                        '$ref' => '#/definitions/User',
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
                    '$ref' => '#/definitions/User',
                ],
                'status' => [
                    'type' => 'string',
                    'title' => 'Whether this user is enabled or disabled.',
                    'description' => 'Only enabled users may be used in actions.',
                    'enum' => ['disabled', 'enabled'],
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
            ],
        ], $this->getModel('JMSUser')->toArray());
    }

    public function testModelComplexDualDocumentation()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'complex' => [
                    '$ref' => '#/definitions/JMSComplex2',
                ],
                'user' => [
                    '$ref' => '#/definitions/JMSUser',
                ],
            ],
        ], $this->getModel('JMSDualComplex')->toArray());
    }

    public function testModelComplexDocumentation()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'user' => ['$ref' => '#/definitions/JMSUser2'],
                'name' => ['type' => 'string'],
                'virtual' => ['$ref' => '#/definitions/JMSUser'],
            ],
            'required' => [
                'id',
                'user',
            ],
        ], $this->getModel('JMSComplex')->toArray());

        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'title' => 'userid',
                    'description' => 'User id',
                    'readOnly' => true,
                    'example' => '1',
                ],
            ],
        ], $this->getModel('JMSUser2')->toArray());
    }

    public function testYamlConfig()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                ],
                'email' => [
                    'type' => 'string',
                ],
            ],
        ], $this->getModel('VirtualProperty')->toArray());
    }

    public function testNamingStrategyWithConstraints()
    {
        $this->assertEquals([
            'type' => 'object',
            'properties' => [
                'beautifulName' => [
                    'type' => 'string',
                    'maxLength' => '10',
                    'minLength' => '3',
                ],
            ],
            'required' => ['beautifulName'],
        ], $this->getModel('JMSNamingStrategyConstraints')->toArray());
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel(true);
    }
}
