<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Tests\Manager;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\DocumentationFilesExtractor;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

/**
 * Class DocumentationFilesManagerTest
 */
class DocumentationFilesManagerTest extends WebTestCase
{
    /**
     * @var DocumentationFilesExtractor
     */
    protected $documentationFilesExtractor;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $apiDocFactory = $this->getContainer()->get('nelmio_api_doc.factory.api_doc');
        $kernel        = $this->getContainer()->get('kernel');

        $configuration = array(
            'enabled' => true,
            'path'    => '/Resources/nelmio'
        );

        $this->documentationFilesExtractor = new DocumentationFilesExtractor($apiDocFactory, $kernel, $configuration);
    }

    /**
     * Test extract files with another directory
     */
    public function testExtractFilesOtherDirectory()
    {
        $apiDocFactory = $this->getContainer()->get('nelmio_api_doc.factory.api_doc');
        $kernel        = $this->getContainer()->get('kernel');

        $configuration = array(
            'enabled' => true,
            'path'    => '/Resources/testOtherDirectory'
        );

        $documentationFilesExtractor = new DocumentationFilesExtractor($apiDocFactory, $kernel, $configuration);

        $array = $documentationFilesExtractor->extractFiles(ApiDoc::DEFAULT_VIEW);

        $this->assertCount(1, $array);
    }

    /**
     * Test extract files with default view works
     */
    public function testExtractFilesDefault()
    {
        $array = $this->documentationFilesExtractor->extractFiles(ApiDoc::DEFAULT_VIEW);

        $this->assertCount(6, $array);
    }

    /**
     * Test extract files with premium view works
     */
    public function testExtractFilesPremium()
    {
        $array = $this->documentationFilesExtractor->extractFiles('premium');

        $this->assertCount(4, $array);
    }
}
