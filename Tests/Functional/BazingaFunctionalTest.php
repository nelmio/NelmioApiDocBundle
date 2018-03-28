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
                    'properties' => [
                        'example' => [
                            '$ref' => '#/definitions/BazingaUserHateoasLinkExample',
                        ],
                        'route' => [
                            '$ref' => '#/definitions/BazingaUserHateoasLinkRoute',
                        ],
                    ],
                ],
                '_embedded' => [
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
