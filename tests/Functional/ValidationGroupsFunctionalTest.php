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

class ValidationGroupsFunctionalTest extends WebTestCase
{
    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_VALIDATION_GROUPS);
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testConstraintGroupsAreRespectedWhenDescribingModels(): void
    {
        $expected = [
            'required' => [
                'property',
            ],
            'properties' => [
                'property' => [
                    'type' => 'integer',
                    // the min/max constraint is in the default group only and shouldn't
                    // be read here with validation groups turned on
                ],
            ],
            'type' => 'object',
            'schema' => 'SymfonyConstraintsTestGroup',
        ];

        self::assertEquals(
            $expected,
            json_decode($this->getModel('SymfonyConstraintsTestGroup')->toJson(), true)
        );
    }

    public function testConstraintDefaultGroupsAreRespectedWhenReadingAnnotations(): void
    {
        $expected = [
            'properties' => [
                'property' => [
                    'type' => 'integer',
                    // min/max will be read here as they are in th e default group
                    'maximum' => 100,
                    'minimum' => 1,
                ],
                'propertyInDefaultGroup' => [
                    'type' => 'integer',
                    // min/max will be read here as they are in th e default group
                    'maximum' => 100,
                    'minimum' => 1,
                ],
                'propertyArray' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
            'type' => 'object',
            'schema' => 'SymfonyConstraintsDefaultGroup',
            'required' => [
                'property',
                'propertyInDefaultGroup',
            ],
        ];

        self::assertEquals(
            $expected,
            json_decode($this->getModel('SymfonyConstraintsDefaultGroup')->toJson(), true)
        );
    }
}
