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

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SwaggerUiTest extends WebTestCase
{
    public function testSwaggerUi()
    {
        $client = self::createClient();
        $crawler = $client->request('GET', '/docs/');

        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $swaggerUiSpec = json_decode($crawler->filterXPath('//script[@id="swagger-data"]')->text(), true);
        $appSpec = $client->getContainer()->get('nelmio_api_doc.generator')->generate()->toArray();
        $this->assertEquals($appSpec, $swaggerUiSpec['spec']);
    }
}
