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
                'id' => ['type' => 'integer'],
                'email' => ['type' => 'string'],
                'roles' => [
                    'type' => 'array',
                    'items' => ['type' => 'string'],
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
            ],
        ], $this->getModel('JMSUser')->toArray());
    }

    protected static function createKernel(array $options = array())
    {
        return new TestKernel(true);
    }
}
