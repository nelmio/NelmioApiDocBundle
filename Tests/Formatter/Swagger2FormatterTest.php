<?php

namespace Nelmio\ApiDocBundle\Tests\Formatter;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Formatter\SwaggerFormatter;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

/**
 * Class SwaggerFormatterTest
 *
 * @package Nelmio\ApiDocBundle\Tests\Formatter
 * @author  Bez Hermoso <bez@activelamp.com>
 */
class Swagger2FormatterTest extends WebTestCase
{

    /**
     * @var ApiDocExtractor
     */
    protected $extractor;

    /**
     * @var SwaggerFormatter
     */
    protected $formatter;

    protected function setUp()
    {
        parent::setUp();

        $container       = $this->getContainer();
        $this->extractor = $container->get('nelmio_api_doc.extractor.api_doc_extractor');
        $this->formatter = $container->get('nelmio_api_doc.formatter.swagger2_formatter');
    }

    public function testResourceListing()
    {
        set_error_handler(array($this, 'handleDeprecation'));
        $data = $this->extractor->all();
        restore_error_handler();

        /** @var $formatter SwaggerFormatter */

        $actual = $this->formatter->format($data, null);

        print_r($actual);

        $expected = array(
            "swagger" => "2.0",
            "info" => array(
                "version" => "3.14",
                "title" => "Nelmio Swagger2",
                "description" => "Testing Swagger2 integration.",
                "termOfService" => "https://github.com/nelmio/NelmioApiDocBundle/blob/master/README.md",
                "contact" => array(
                    "name" => "Bez Hermoso",
                    "email" => "bezalelhermoso@gmail.com",
                    "url" => "https://github.com/nelmio/NelmioApiDocBundle"
                ),
                "license" => array(
                    "name" => "MIT",
                    "url" => "https://opensource.org/licenses/MIT"
                )
            ),
            "host" => "",
            "basePath" => "/api",
            "schemes" => array("http", "https"),
            "consumes" => array("application/json", "text/xml"),
            "produces" => array("application/json", "text/xml"),
            "paths" => array(
                "/tests.{_format}" => array(
                    "get" => array(
                    ),
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/another" => array(
                    "get" => array(
                    ),
                ),
                "/any/{foo}" => array(
                    "get" => array(
                    ),
                ),
                "/my-commented/{id}/{page}/{paramType}/{param}" => array(
                    "get" => array(
                    ),
                ),
                "/yet-another/{id}" => array(
                    "get" => array(
                    ),
                ),
                "/another-post" => array(
                    "post" => array(
                    ),
                ),
                "/z-action-with-query-param" => array(
                    "get" => array(
                    ),
                ),
                "/jms-return-test" => array(
                    "get" => array(
                    ),
                ),
                "/jms-input-test" => array(
                    "post" => array(
                    ),
                ),
                "/z-action-with-request-param" => array(
                    "post" => array(
                    ),
                ),
                "/secure-route" => array(
                    "get" => array(
                    ),
                ),
                "/authenticated" => array(
                    "get" => array(
                    ),
                ),
                "/tests.{_format}" => array(
                    "get" => array(
                    ),
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/any" => array(
                    "get" => array(
                    ),
                ),
                "/tests2.{_format}" => array(
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/z-action-with-query-param-strict" => array(
                    "get" => array(
                    ),
                ),
                "/z-action-with-query-param-no-default" => array(
                    "get" => array(
                    ),
                ),
                "/z-action-with-deprecated-indicator" => array(
                    "get" => array(
                    ),
                ),
                "/return-nested-output" => array(
                    "get" => array(
                    ),
                ),
                "/return-nested-extend-output" => array(
                    "get" => array(
                    ),
                ),
                "/z-return-jms-and-validator-output" => array(
                    "get" => array(
                    ),
                ),
                "/named-resource" => array(
                    "get" => array(
                    ),
                ),
                "/z-return-selected-parsers-output" => array(
                    "get" => array(
                    ),
                ),
                "/z-return-selected-parsers-input" => array(
                    "get" => array(
                    ),
                ),
                "/private" => array(
                    "get" => array(
                    ),
                ),
                "/exclusive" => array(
                    "get" => array(
                    ),
                ),
                "/z-action-with-constraint-requirements" => array(
                    "get" => array(
                    ),
                ),
                "/z-action-with-nullable-request-param" => array(
                    "post" => array(
                    ),
                ),
                "/resources.{_format}" => array(
                    "get" => array(
                    ),
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/resources/{id}.{_format}" => array(
                    "get" => array(
                    ),
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/other-resources.{_format}" => array(
                    "put" => array(
                    ),
                    "patch" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/other-resources/{id}.{_format}" => array(
                    "get" => array(
                    ),
                    "post" => array(
                    ),
                    "parameters" => array(
                        array(
                            "name" => "id",
                            "type" => "string",
                            "in" => "path",
                            "required" => false,
                            "description" => "",
                        )
                    ),
                ),
                "/zcached" => array(
                    "post" => array(
                    ),
                ),
                "/zsecured" => array(
                    "get" => array(
                    ),
                ),
                "/with-link" => array(
                    "get" => array(
                    ),
                ),
                "/z-action-with-array-request-param" => array(
                    "post" => array(
                    ),
                ),
                "/override/properties" => array(
                    "post" => array(
                    ),
                    "put" => array(
                    ),
                ),
                "/popos" => array(
                    "get" => array(
                    )
                ),
                "/popos/{id}" => array(
                    "get" => array(
                    )
                ),
            ),
            "definitions" => array(

            ),
        );

        $this->assertEquals($expected, $actual);

    }
}
