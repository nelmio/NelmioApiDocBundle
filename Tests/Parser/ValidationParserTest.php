<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Tests\WebTestCase;
use Nelmio\ApiDocBundle\Parser\ValidationParser;
use Nelmio\ApiDocBundle\Parser\ValidationParserLegacy;
use Symfony\Component\HttpKernel\Kernel;

class ValidationParserTest extends WebTestCase
{
    protected $handler;

    public function setUp()
    {
        $container  = $this->getContainer();
        $factory = $container->get('validator')->getMetadataFactory();

        if (version_compare(Kernel::VERSION, '2.2.0', '<')) {
            $this->parser = new ValidationParserLegacy($factory);
        } else {
            $this->parser = new ValidationParser($factory);
        }
    }

    /**
     * @dataProvider dataTestParser
     */
    public function testParser($property, $expected)
    {
        $result = $this->parser->parse(array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\ValidatorTest'));
        foreach ($expected as $name => $value) {
            $this->assertArrayHasKey($property, $result);
            $this->assertArrayHasKey($name, $result[$property]);
            $this->assertEquals($result[$property][$name], $expected[$name]);
        }
    }

    public function dataTestParser()
    {
        return array(
            array(
                'property' => 'length10',
                'expected' => array(
                    'format' => '{length: min: 10}',
                    'default' => 'validate this',
                )
            ),
            array(
                'property' => 'length1to10',
                'expected' => array(
                    'format' => '{length: min: 1, max: 10}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'notblank',
                'expected' => array(
                    'required' => true,
                    'default' => null,
                )
            ),
            array(
                'property' => 'notnull',
                'expected' => array(
                    'required' => true,
                    'default' => null,
                )
            ),
            array(
                'property' => 'type',
                'expected' => array(
                    'dataType' => 'DateTime',
                    'actualType' => DataTypes::DATETIME,
                    'default' => null,
                )
            ),
            array(
                'property' => 'date',
                'expected' => array(
                    'format' => '{Date YYYY-MM-DD}',
                    'actualType' => DataTypes::DATE,
                    'default' => null,
                )
            ),
            array(
                'property' => 'dateTime',
                'expected' => array(
                    'format' => '{DateTime YYYY-MM-DD HH:MM:SS}',
                    'actualType' => DataTypes::DATETIME,
                    'default' => null,
                )
            ),
            array(
                'property' => 'time',
                'expected' => array(
                    'format' => '{Time HH:MM:SS}',
                    'actualType' => DataTypes::TIME,
                    'default' => null,
                )
            ),
            array(
                'property' => 'email',
                'expected' => array(
                    'format' => '{email address}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'url',
                'expected' => array(
                    'format' => '{url}',
                    'default' => 'https://github.com',
                )
            ),
            array(
                'property' => 'ip',
                'expected' => array(
                    'format' => '{ip address}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'singlechoice',
                'expected' => array(
                    'format' => '[a|b]',
                    'actualType' => DataTypes::ENUM,
                    'default' => null,
                )
            ),
            array(
                'property' => 'multiplechoice',
                'expected' => array(
                    'format' => '{choice of [x|y|z]}',
                    'actualType' => DataTypes::COLLECTION,
                    'subType' => DataTypes::ENUM,
                    'default' => null,
                )
            ),
            array(
                'property' => 'multiplerangechoice',
                'expected' => array(
                    'format' => '{min: 2 max: 3 choice of [foo|bar|baz|qux]}',
                    'actualType' => DataTypes::COLLECTION,
                    'subType' => DataTypes::ENUM,
                    'default' => null,
                )
            ),
            array(
                'property' => 'regexmatch',
                'expected' => array(
                    'format' => '{match: /^\d{1,4}\w{1,4}$/}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'regexnomatch',
                'expected' => array(
                    'format' => '{not match: /\d/}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'multipleassertions',
                'expected' => array(
                    'required' => true,
                    'dataType' => 'string',
                    'format' => '{email address}',
                    'default' => null,
                )
            ),
            array(
                'property' => 'multipleformats',
                'expected' => array(
                    'format' => '{url}, {length: min: 10}',
                    'default' => null,
                )
            )
        );
    }
}
