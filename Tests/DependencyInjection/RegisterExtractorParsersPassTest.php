<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\DependencyInjection;

use Nelmio\ApiDocBundle\Tests\WebTestCase;

class RegisterExtractorParsersPassTest extends WebTestCase
{

    public function testParsersAreEnabledInConfigByDefault()
    {
        $container = $this->getContainer();
        $parserList = $container->getParameter('nelmio_api_doc.parsers');

        $this->assertNotEmpty($parserList);

        foreach ($parserList as $parser => $enabled) {
            $this->assertTrue(
                $enabled,
                sprintf('The Parser %s is not enabled', $parser)
            );
        }
    }

    public function testParsersAreEnabledInExtractorByDefault()
    {
        $container = $this->getContainer();
        $configParsers = $container->getParameter('nelmio_api_doc.parsers');
        $extractorParsersRaw = $container->get('nelmio_api_doc.extractor.api_doc_extractor')->getParser();

        $this->assertNotEmpty($extractorParsersRaw);

        $extractorParsers = array_map(function($value) {
            $class = get_class($value);
            $class = explode('\\', $class);
            $class = array_pop($class);
            return $class;
        }, $extractorParsersRaw);

        foreach ($configParsers as $parser => $enabled) {
            if ($enabled) {
                $this->assertTrue(in_array($parser, $extractorParsers));
            } else {
                $this->assertFalse(in_array($parser, $extractorParsers));
            }
        }

    }
}
