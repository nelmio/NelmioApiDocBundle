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

use OpenApi\Annotations\Server;
use OpenApi\Context;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SwaggerUiTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient([], ['HTTP_HOST' => 'api.example.com', 'PHP_SELF' => '/app_dev.php/default/docs', 'SCRIPT_FILENAME' => '/var/www/app/web/app_dev.php']);
    }

    public function testSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/default/docs');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('UTF-8', $response->getCharset());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testRedocly()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/default/redocly/docs');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('UTF-8', $response->getCharset());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        $this->assertSame(1, $crawler->filterXPath('//script[@src="/bundles/nelmioapidoc/redocly/redoc.standalone.js"]')->count());
        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testApiPlatformSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/test/docs');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition('test')->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testJsonDocs()
    {
        $this->client->request('GET', '/app_dev.php/default/docs.json');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testInvalidJsonArea()
    {
        $this->client->request('GET', '/app_dev.php/nonexistent/docs.json');

        $response = $this->client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testYamlDocs()
    {
        $this->client->request('GET', '/app_dev.php/default/docs.yaml');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/x-yaml; charset=UTF-8', $response->headers->get('Content-Type'));

        $spec = $this->getOpenApiDefinition();
        $spec->servers = [new Server(['url' => 'http://api.example.com/app_dev.php', '_context' => new Context()])];
        $expected = $spec->toYaml();

        $this->assertEquals($expected, $response->getContent());
    }
}
