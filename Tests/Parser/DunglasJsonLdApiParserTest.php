<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DunglasJsonLdApiParserTest extends WebTestCase
{
    protected function setUp()
    {
        if (!class_exists('Dunglas\JsonLdApiBundle\DunglasJsonLdApiBundle')) {
            $this->markTestSkipped(
                'DunglasJsonLdApiBundle is not available.'
            );
        }
    }

    public function testParser()
    {
        $container = $this->getContainer();
        $parser = $container->get('nelmio_api_doc.parser.dunglas_json_ld_api_parser');

        $item = array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Popo');

        $expected = array (
            'foo' =>
                array (
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Popo',
                    'dataType' => 'string',
                ),
        );

        $this->assertTrue($parser->supports($item));
        $this->assertEquals($expected, $parser->parse($item));
    }
}
