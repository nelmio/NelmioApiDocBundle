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

        $this->client = static::createClient([], ['HTTP_HOST' => 'api.example.com', 'PHP_SELF' => '/app_dev.php/docs', 'SCRIPT_FILENAME' => '/var/www/app/web/app_dev.php']);
    }

    public function testSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/docs');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('UTF-8', $response->getCharset());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testApiPlatformSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/docs/test');

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
        $this->client->request('GET', '/app_dev.php/docs.json');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $expected = json_decode($this->getOpenApiDefinition()->toJson(), true);
        $expected['servers'] = [
            ['url' => 'http://api.example.com/app_dev.php'],
        ];

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }

    public function testYamlDocs()
    {
        $this->client->request('GET', '/app_dev.php/docs.yaml');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/x-yaml; charset=UTF-8', $response->headers->get('Content-Type'));

        $spec = $this->getOpenApiDefinition();
        $spec->servers = [new Server(['url' => 'http://api.example.com/app_dev.php'])];
        $expected = $spec->toYaml();

        $this->assertEquals($expected, $response->getContent());
    }
}
