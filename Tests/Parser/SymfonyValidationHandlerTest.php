<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Tests\WebTestCase;
use Nelmio\ApiDocBundle\Parser\Handler\SymfonyValidationHandler;


class SymfonyValidationHandlerTest extends WebTestCase
{
    protected $handler;

    public function setUp()
    {
        $container  = $this->getContainer();
        $factory = $container->get('validator.mapping.class_metadata_factory');

        $this->handler = new SymfonyValidationHandler($factory);
    }

    /**
     * @dataProvider dataTestHandler
     */
    public function testHandler($property, $expected)
    {
        $result = $this->handler->handle('Nelmio\ApiDocBundle\Tests\Fixtures\Model\ValidatorTest', $property, array());

        $this->assertEquals($expected, $result);
    }


    public function dataTestHandler()
    {
        return array(
            array(
                'property' => 'length10',
                'expected' => array(
                    'format' => '{length: min: 10}'
                )
            ),
            array(
                'property' => 'length1to10',
                'expected' => array(
                    'format' => '{length: min: 1, max: 10}'
                )
            ),
            array(
                'property' => 'notblank',
                'expected' => array(
                    'required' => true
                )
            ),
            array(
                'property' => 'notnull',
                'expected' => array(
                    'required' => true
                )
            ),
            array(
                'property' => 'type',
                'expected' => array(
                    'dataType' => 'DateTime'
                )
            ),
            array(
                'property' => 'email',
                'expected' => array(
                    'format' => '{email address}'
                )
            ),
            array(
                'property' => 'url',
                'expected' => array(
                    'format' => '{url}'
                )
            ),
            array(
                'property' => 'ip',
                'expected' => array(
                    'format' => '{ip address}'
                )
            ),
            array(
                'property' => 'singlechoice',
                'expected' => array(
                    'format' => '[a|b]'
                )
            ),
            array(
                'property' => 'multiplechoice',
                'expected' => array(
                    'format' => '{choice of [x|y|z]}'
                )
            ),
            array(
                'property' => 'multiplerangechoice',
                'expected' => array(
                    'format' => '{min: 2 max: 3 choice of [foo|bar|baz|qux]}'
                )
            ),
            array(
                'property' => 'regexmatch',
                'expected' => array(
                    'format' => '{match: /^\d{1,4}\w{1,4}$/}'
                )
            ),
            array(
                'property' => 'regexnomatch',
                'expected' => array(
                    'format' => '{not match: /\d/}'
                )
            ),
            array(
                'property' => 'multipleassertions',
                'expected' => array(
                    'required' => true,
                    'dataType' => 'string',
                    'format' => '{email address}'
                )
            ),
            array(
                'property' => 'multipleformats',
                'expected' => array(
                    'format' => '{url}, {length: min: 10}'
                )
            )
        );
    }
}
