<?php

namespace Nelmio\ApiDocBundle\Tests\Swagger2;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Swagger2\ModelRegistry;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

/**
 * Class SwaggerFormatterTest
 *
 * @package Nelmio\ApiDocBundle\Tests\Formatter
 * @author  Bez Hermoso <bez@activelamp.com>
 */
class SwaggerFormatterTest extends WebTestCase
{

    /**
     * @var ApiDocExtractor
     */
    protected $extractor;

    protected function setUp()
    {
        parent::setUp();

        $container       = $this->getContainer();
        $this->extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
    }

    public  function testRegister()
    {
        $registry = new ModelRegistry();

        $params = array(
            ''
        );
    }
}
