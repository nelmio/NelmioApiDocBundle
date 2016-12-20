<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Extractor;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\CachingApiDocExtractor;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

class CachingApiDocExtractorTest extends WebTestCase
{
    /**
     * @return array
     */
    public static function viewsWithoutDefaultProvider()
    {
        $data = ApiDocExtractorTest::dataProviderForViews();
        // remove default view data from provider
        array_shift($data);

        return $data;
    }

    /**
     * Test that every view cache is saved in its own cache file
     *
     * @dataProvider viewsWithoutDefaultProvider
     * @param string $view View name
     */
    public function testDifferentCacheFilesAreCreatedForDifferentViews($view)
    {
        $container = $this->getContainer();
        /* @var CachingApiDocExtractor $extractor */
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $this->assertInstanceOf('\Nelmio\ApiDocBundle\Extractor\CachingApiDocExtractor', $extractor);

        set_error_handler(array($this, 'handleDeprecation'));
        $defaultData = $extractor->all(ApiDoc::DEFAULT_VIEW);
        $data = $extractor->all($view);
        restore_error_handler();

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $data);
        $this->assertNotSameSize($defaultData, $data);
        $this->assertNotEquals($defaultData, $data);

        $cacheFile = $container->getParameter('kernel.cache_dir').'/api-doc.cache';

        $expectedDefaultViewCacheFile = $cacheFile.'.'.ApiDoc::DEFAULT_VIEW;
        $expectedViewCacheFile = $cacheFile.'.'.$view;

        $this->assertFileExists($expectedDefaultViewCacheFile);
        $this->assertFileExists($expectedViewCacheFile);
        $this->assertFileNotEquals($expectedDefaultViewCacheFile, $expectedViewCacheFile);
    }

    /**
     * @dataProvider \Nelmio\ApiDocBundle\Tests\Extractor\ApiDocExtractorTest::dataProviderForViews
     * @param string $view View name to test
     */
    public function testCachedResultSameAsGenerated($view)
    {
        $container = $this->getContainer();
        /* @var CachingApiDocExtractor $extractor */
        $extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $this->assertInstanceOf('\Nelmio\ApiDocBundle\Extractor\CachingApiDocExtractor', $extractor);

        $cacheFile = $container->getParameter('kernel.cache_dir').'/api-doc.cache';

        $expectedViewCacheFile = $cacheFile.'.'.$view;

        set_error_handler(array($this, 'handleDeprecation'));
        $data = $extractor->all($view);

        $this->assertFileExists($expectedViewCacheFile);

        $cachedData = $extractor->all($view);
        restore_error_handler();

        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $data);
        $this->assertInternalType(\PHPUnit_Framework_Constraint_IsType::TYPE_ARRAY, $cachedData);
        $this->assertSameSize($data, $cachedData);
        $this->assertEquals($data, $cachedData);
    }
}
