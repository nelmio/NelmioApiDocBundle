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
            ],
        ], $this->getModel('JMSUser')->toArray());
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

    protected static function createKernel(array $options = [])
    {
        return new TestKernel(true);
    }
}
