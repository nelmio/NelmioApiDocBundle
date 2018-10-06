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
    protected static function createClient(array $options = [], array $server = [])
    {
        return parent::createClient([], $server + ['HTTP_HOST' => 'api.example.com', 'PHP_SELF' => '/app_dev.php/docs', 'SCRIPT_FILENAME' => '/var/www/app/web/app_dev.php']);
    }

    /**
     * @dataProvider areaProvider
     */
    public function testSwaggerUi($url, $area, $expected)
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/app_dev.php'.$url);

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
    }

    public function areaProvider()
    {
        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';

        yield ['/docs', 'default', $expected];

        // Api-platform documentation
        $expected['info']['title'] = 'My Test App';
        $expected['paths'] = [
            '/api/dummies' => $expected['paths']['/api/dummies'],
            '/api/foo' => $expected['paths']['/api/foo'],
            '/api/dummies/{id}' => $expected['paths']['/api/dummies/{id}'],
            '/test/test/' => ['get' => [
                'responses' => ['200' => ['description' => 'Test']],
            ]],
        ];
        $expected['definitions'] = ['Dummy' => $expected['definitions']['Dummy'], 'Test' => ['type' => 'string']];

        yield ['/docs/test', 'test', $expected];
    }

    public function testJsonDocs()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/app_dev.php/docs.json');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));

        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';

        $this->assertEquals($expected, json_decode($response->getContent(), true));
    }
}
