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

class NameConverterFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testContextPassedToNameConverter()
    {
        $this->getOperation('/api/name_converter_context', 'get');

        $model = $this->getModel('EntityThroughNameConverter');
        $this->assertCount(2, $model->properties);
        $this->assertNotHasProperty('id', $model);
        $this->assertHasProperty('name_converter_context_id', $model);
        $this->assertNotHasProperty('name', $model);
        $this->assertHasProperty('name_converter_context_name', $model);
    }
}
