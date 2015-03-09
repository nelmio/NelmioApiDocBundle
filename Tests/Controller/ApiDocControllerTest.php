<?php
/**
 * Created by PhpStorm.
 * User: bezalelhermoso
 * Date: 6/27/14
 * Time: 4:35 PM
 */

namespace NelmioApiDocBundle\Tests\Controller;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

/**
 * Class ApiDocControllerTest
 *
 * @package NelmioApiDocBundle\Tests\Controller
 * @author Bez Hermoso <bez@activelamp.com>
 */
class ApiDocControllerTest extends WebTestCase
{
    public function testSwaggerDocResourceListRoute()
    {
        $client = static::createClient();
        $client->request('GET', '/api-docs/');

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));

    }

    public function dataTestApiDeclarations()
    {
        return array(
            array('resources'),
            array('tests'),
            array('tests2'),
            array('TestResource'),
        );
    }

    /**
     * @dataProvider dataTestApiDeclarations
     */
    public function testApiDeclarationRoutes($resource)
    {
        $client = static::createClient();
        $client->request('GET', '/api-docs/' . $resource);

        $response = $client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-type'));
    }

    public function testNonExistingApiDeclaration()
    {
        $client = static::createClient();
        $client->request('GET', '/api-docs/santa');

        $response = $client->getResponse();
        $this->assertEquals(404, $response->getStatusCode());

    }
}
