<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace JlpovedaApiDocBundle\Tests\Parser;

use Jlpoveda\ApiDocBundle\DataTypes;
use Jlpoveda\ApiDocBundle\Parser\DunglasApiParser;
use Jlpoveda\ApiDocBundle\Tests\WebTestCase;

/**
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DunglasApiParserTest extends WebTestCase
{
    protected function setUp()
    {
        if (!class_exists('Dunglas\ApiBundle\DunglasApiBundle')) {
            $this->markTestSkipped(
                'DunglasApiBundle is not available.'
            );
        }
    }

    public function testParser()
    {
        $container = $this->getContainer();
        $parser = $container->get('nelmio_api_doc.parser.dunglas_api_parser');

        $item = array('class' => DunglasApiParser::OUT_PREFIX.':Nelmio\ApiDocBundle\Tests\Fixtures\Model\Popo');

        $expected = array (
            'foo' =>
                array (
                    'required' => false,
                    'description' => '',
                    'readonly' => false,
                    'dataType' => DataTypes::STRING,
                ),
        );

        $this->assertTrue($parser->supports($item));
        $this->assertEquals($expected, $parser->parse($item));
    }
}
