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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function testSwaggerUi(): void
    {
        $crawler = $this->client->request('GET', '/app_dev.php/default/docs');

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('UTF-8', $response->getCharset());
        self::assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        self::assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testRedocly(): void
    {
        $crawler = $this->client->request('GET', '/app_dev.php/default/redocly/docs');

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('UTF-8', $response->getCharset());
        self::assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        self::assertCount(1, $crawler->filterXPath('//script[@src="/bundles/nelmioapidoc/redocly/redoc.standalone.js"]'));
        self::assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testScalar(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/app_dev.php/default/scalar/docs');

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertEquals('UTF-8', $response->getCharset());
        self::assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        self::assertCount(1, $crawler->filterXPath('//script[@src="/bundles/nelmioapidoc/scalar/scalar.standalone.js"]'));
        self::assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="api-reference"]')->text(), true));
    }

    public function testApiPlatformSwaggerUi(): void
    {
        $crawler = $this->client->request('GET', '/app_dev.php/test/docs');

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition('test')->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        self::assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testJsonDocs(): void
    {
        $this->client->request('GET', '/app_dev.php/default/docs.json');

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('application/json', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        self::assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testInvalidJsonArea(): void
    {
        $this->client->request('GET', '/app_dev.php/nonexistent/docs.json');

        $response = $this->client->getResponse();
        self::assertEquals(400, $response->getStatusCode());
    }

    public function testYamlDocs(): void
    {
        $this->client->request('GET', '/app_dev.php/default/docs.yaml');

        $response = $this->client->getResponse();
        self::assertEquals(200, $response->getStatusCode());
        self::assertEquals('text/x-yaml; charset=UTF-8', $response->headers->get('Content-Type'));

        $spec = $this->getOpenApiDefinition();
        $spec->servers = [new Server(['url' => 'http://api.example.com/app_dev.php', '_context' => new Context()])];
        $expected = $spec->toYaml();

        self::assertEquals($expected, $response->getContent());
    }
}
