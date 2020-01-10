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

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

class SwaggerUiTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private $client;

    protected function setUp()
    {
        parent::setUp();

        $this->client = static::createClient([], ['HTTP_HOST' => 'api.example.com', 'PHP_SELF' => '/app_dev.php/docs', 'SCRIPT_FILENAME' => '/var/www/app/web/app_dev.php']);
    }

    public function testSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/docs');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testApiPlatformSwaggerUi()
    {
        $crawler = $this->client->request('GET', '/app_dev.php/docs/test');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';
        $expected['info']['title'] = 'My Test App';
        $expected['paths'] = [
            '/api/dummies' => $expected['paths']['/api/dummies'],
            '/api/foo' => $expected['paths']['/api/foo'],
            '/api/dummies/{id}' => $expected['paths']['/api/dummies/{id}'],
            '/test/test/' => ['get' => [
                'responses' => ['200' => ['description' => 'Test']],
            ]],
            '/test/test/{id}' => ['get' => [
                'responses' => ['200' => ['description' => 'Test Ref']],
                'parameters' => [['$ref' => '#/parameters/test']],
            ]],
        ];
        $expected['definitions'] = [
            'Dummy' => $expected['definitions']['Dummy'],
            'Test' => ['type' => 'string'],
            'JMSPicture_mini' => ['type' => 'object'],
            'BazingaUser_grouped' => ['type' => 'object'],
        ];

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function testJsonDocs()
    {
        $this->client->request('GET', '/app_dev.php/docs.json');

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';
        $expected['host'] = 'api.example.com';

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }
}
