<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested;
use Nelmio\ApiDocBundle\Parser\JmsMetadataParser;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\CamelCaseNamingStrategy;

class JmsMetadataParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataTestParserWithNestedType
     */
    public function testParserWithNestedType($type)
    {
        $metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $docCommentExtractor = $this->getMockBuilder('Nelmio\ApiDocBundle\Util\DocCommentExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMetadataFoo = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'foo');
        $propertyMetadataFoo->type = array(
            'name' => 'DateTime'
        );

        $propertyMetadataBar = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'bar');
        $propertyMetadataBar->type = array(
            'name' => 'string'
        );

        $propertyMetadataBaz = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'baz');
        $propertyMetadataBaz->type = array(
            'name' => $type,
            'params' =>  array(
                array(
                    'name' => 'integer',
                    'params' => array()
                )
            )
        );

        $metadata = new ClassMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested');
        $metadata->addPropertyMetadata($propertyMetadataFoo);
        $metadata->addPropertyMetadata($propertyMetadataBar);
        $metadata->addPropertyMetadata($propertyMetadataBaz);

        $propertyNamingStrategy = $this->getMock('JMS\Serializer\Naming\PropertyNamingStrategyInterface');

        $propertyNamingStrategy
            ->expects($this->at(0))
            ->method('translateName')
            ->will($this->returnValue('foo'));

        $propertyNamingStrategy
            ->expects($this->at(1))
            ->method('translateName')
            ->will($this->returnValue('bar'));

        $propertyNamingStrategy
            ->expects($this->at(2))
            ->method('translateName')
            ->will($this->returnValue('baz'));

        $input = 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested';

        $metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with($input)
            ->will($this->returnValue($metadata));

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);

        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array(),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'DateTime',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'baz' => array(
                    'dataType'    => 'array of integers',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                )
            ),
            $output
        );
    }

    public function testParserWithGroups()
    {
        $metadataFactory     = $this->getMock('Metadata\MetadataFactoryInterface');
        $docCommentExtractor = $this->getMockBuilder('Nelmio\ApiDocBundle\Util\DocCommentExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMetadataFoo       = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'foo');
        $propertyMetadataFoo->type = array('name' => 'string');

        $propertyMetadataBar         = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'bar');
        $propertyMetadataBar->type   = array('name' => 'string');
        $propertyMetadataBar->groups = array('Default', 'Special');

        $propertyMetadataBaz         = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'baz');
        $propertyMetadataBaz->type   = array('name' => 'string');
        $propertyMetadataBaz->groups = array('Special');

        $input = 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested';

        $metadata = new ClassMetadata($input);
        $metadata->addPropertyMetadata($propertyMetadataFoo);
        $metadata->addPropertyMetadata($propertyMetadataBar);
        $metadata->addPropertyMetadata($propertyMetadataBaz);

        $metadataFactory->expects($this->any())
            ->method('getMetadataForClass')
            ->with($input)
            ->will($this->returnValue($metadata));

        $propertyNamingStrategy = new CamelCaseNamingStrategy();

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);

        // No group specified.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array(),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );

        // Default group.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Default'),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );

        // Special group.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Special'),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'baz' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );

        // Default + Special groups.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Default', 'Special'),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'baz' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                )
            ),
            $output
        );
    }

    public function testParserWithVersion()
    {
        $metadataFactory     = $this->getMock('Metadata\MetadataFactoryInterface');
        $docCommentExtractor = $this->getMockBuilder('Nelmio\ApiDocBundle\Util\DocCommentExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMetadataFoo       = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'foo');
        $propertyMetadataFoo->type = array('name' => 'string');

        $propertyMetadataBar               = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'bar');
        $propertyMetadataBar->type         = array('name' => 'string');
        $propertyMetadataBar->sinceVersion = '0.2';
        $propertyMetadataBar->untilVersion = '0.3';

        $input = 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested';

        $metadata = new ClassMetadata($input);
        $metadata->addPropertyMetadata($propertyMetadataFoo);
        $metadata->addPropertyMetadata($propertyMetadataBar);

        $metadataFactory->expects($this->any())
            ->method('getMetadataForClass')
            ->with($input)
            ->will($this->returnValue($metadata));

        $propertyNamingStrategy = new CamelCaseNamingStrategy();

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);

        // No version specified.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array(),
                'version' => null,
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
                'bar' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );

        // 0.1
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array(),
                'version' => '0.1',
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );

        // 0.2 & 0.3
        foreach (array('0.2', '0.3') as $version) {
            $output = $jmsMetadataParser->parse(
                array(
                    'class'   => $input,
                    'groups'  => array(),
                    'version' => $version,
                )
            );

            $this->assertEquals(
                array(
                    'foo' => array(
                        'dataType'    => 'string',
                        'required'    => false,
                        'description' => 'No description.',
                        'readonly'    => false
                    ),
                    'bar' => array(
                        'dataType'    => 'string',
                        'required'    => false,
                        'description' => 'No description.',
                        'readonly'    => false
                    ),
                ),
                $output
            );
        }

        // 0.4
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array(),
                'version' => '0.4',
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'    => 'string',
                    'required'    => false,
                    'description' => 'No description.',
                    'readonly'    => false
                ),
            ),
            $output
        );
    }

    public function dataTestParserWithNestedType()
    {
        return array(
            array('array'),
            array('ArrayCollection')
        );
    }
}
