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

class BazingaFunctionalTest extends WebTestCase
{
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
                    ],
                ],
            ],
        ], $this->getModel('BazingaUser')->toArray());
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel(true, true);
    }
}
