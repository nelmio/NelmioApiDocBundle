<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\EventListener;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

class RequestListenerTest extends WebTestCase
{
    public function testDocQueryArg()
    {
        $client = $this->createClient();

        $crawler = $client->request('GET', '/tests?_doc=1');
        $this->assertEquals('/tests.{_format}', trim($crawler->filter(".operation .path:contains('/tests')")->text()), 'Event listener should capture ?_doc=1 requests');

        $client->request('GET', '/tests');
        $this->assertEquals('tests', $client->getResponse()->getContent(), 'Event listener should let normal requests through');
    }
}
