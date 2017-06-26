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

class SwaggerUiTest extends WebTestCase
{
    public function testSwaggerUi()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/docs/');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $swaggerUiSpec = json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true);
        $this->assertEquals($this->getSwaggerDefinition()->toArray(), $swaggerUiSpec['spec']);
    }

    public function testJsonDocs()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/docs.json');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $this->assertEquals($this->getSwaggerDefinition()->toArray(), json_decode($response->getContent(), true));
    }
}
