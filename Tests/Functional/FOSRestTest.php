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

use FOS\RestBundle\FOSRestBundle;

class FOSRestTest extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testFOSRestAction()
    {
        if (!class_exists(FOSRestBundle::class)) {
            $this->markTestSkipped('FOSRestBundle is not installed.');
        }

        $operation = $this->getOperation('/api/fosrest', 'post');

        $parameters = $operation->getParameters();
        $this->assertTrue($parameters->has('foo', 'query'));
        $this->assertTrue($parameters->has('body', 'body'));
        $body = $parameters->get('body', 'body')->getSchema()->getProperties();

        $this->assertTrue($body->has('bar'));
        $this->assertTrue($body->has('baz'));

        $fooParameter = $parameters->get('foo', 'query');
        $this->assertNotNull($fooParameter->getPattern());
        $this->assertEquals('\d+', $fooParameter->getPattern());
        $this->assertNull($fooParameter->getFormat());

        $barParameter = $body->get('bar');
        $this->assertNotNull($barParameter->getPattern());
        $this->assertEquals('\d+', $barParameter->getPattern());
        $this->assertNull($barParameter->getFormat());

        $bazParameter = $body->get('baz');
        $this->assertNotNull($bazParameter->getFormat());
        $this->assertEquals('IsTrue', $bazParameter->getFormat());
        $this->assertNull($bazParameter->getPattern());

        // The _format path attribute should be removed
        $this->assertFalse($parameters->has('_format', 'path'));
    }
}
