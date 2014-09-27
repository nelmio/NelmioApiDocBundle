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
    public function testParsersAreEnabledInExtractorByDefault()
    {
        $container = $this->getContainer();
        $container->setParameter('nelmio_api_doc.parsers', []);
        $extractorParsersRaw = $container->get('nelmio_api_doc.extractor.api_doc_extractor')->getParser();

        $this->assertNotEmpty($extractorParsersRaw);
    }
}
