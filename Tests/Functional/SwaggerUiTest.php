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
        return parent::createClient([], ['PHP_SELF' => '/app_dev.php/docs', 'SCRIPT_FILENAME' => '/var/www/app/web/app_dev.php']);
    }

    public function testSwaggerUi()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/app_dev.php/docs/');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $expected = $this->getSwaggerDefinition()->toArray();
        $expected['basePath'] = '/app_dev.php';

        $this->assertEquals($expected, json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true)['spec']);
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
