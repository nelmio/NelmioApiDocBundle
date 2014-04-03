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
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'DateTime',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'baz' => array(
                    'dataType'     => 'array of integers',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
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
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'baz' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
            ),
            $output
        );

        // Default group.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Default'),
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
            ),
            $output
        );

        // Special group.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Special'),
            )
        );

        $this->assertEquals(
            array(
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'baz' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
            ),
            $output
        );

        // Default + Special groups.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $input,
                'groups'  => array('Default', 'Special'),
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'baz' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
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
        $propertyMetadataBar->sinceVersion = '2.0';

        $propertyMetadataBaz               = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'baz');
        $propertyMetadataBaz->type         = array('name' => 'string');
        $propertyMetadataBaz->untilVersion = '3.0';

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
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'bar' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => '2.0',
                    'untilVersion' => null,
                ),
                'baz' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => '3.0',
                )
            ),
            $output
        );
    }

    public function testParserWithDiscriminator()
    {
        $metadataFactory     = $this->getMock('Metadata\MetadataFactoryInterface');
        $docCommentExtractor = $this->getMockBuilder('Nelmio\ApiDocBundle\Util\DocCommentExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $inputMainClass = 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsWithDiscriminators';
        $inputDiscriminatorClass = 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\DiscriminatorClass';
        $discriminatorFieldName = 'type';

        $propertyMetadataFoo       = new PropertyMetadata($inputMainClass, 'foo');
        $propertyMetadataFoo->type = array(
            'name' => 'string',
        );

        $propertyMetadataBar        = new PropertyMetadata($inputDiscriminatorClass, 'bar');
        $propertyMetadataBar->type  = array(
            'name' => 'string',
        );

        $metadataMainClass = new ClassMetadata($inputMainClass);
        $metadataMainClass->addPropertyMetadata($propertyMetadataFoo);
        $metadataMainClass->setDiscriminator($discriminatorFieldName,
            array(
                'TYPE_1' => $inputDiscriminatorClass,
            )
        );

        $metadataDiscriminatorClass = new ClassMetadata($inputDiscriminatorClass);
        $metadataDiscriminatorClass->addPropertyMetadata($propertyMetadataFoo);
        $metadataDiscriminatorClass->addPropertyMetadata($propertyMetadataBar);

        $metadataFactory->expects($this->any())
            ->method('getMetadataForClass')
            ->will($this->returnValueMap(
                array(
                    array($inputMainClass, $metadataMainClass),
                    array($inputDiscriminatorClass, $metadataDiscriminatorClass)
                )
            ));

        $propertyNamingStrategy = new CamelCaseNamingStrategy();

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);

        // No group specified.
        $output = $jmsMetadataParser->parse(
            array(
                'class'   => $inputMainClass,
                'groups'  => array(),
            )
        );

        $this->assertEquals(
            array(
                'foo' => array(
                    'dataType'     => 'string',
                    'required'     => false,
                    'description'  => null,
                    'readonly'     => false,
                    'sinceVersion' => null,
                    'untilVersion' => null,
                ),
                'Nelmio\ApiDocBundle\Tests\Fixtures\Model\DiscriminatorClass' => array(
                    'dataType'     => 'discriminatorClass',
                    'required'     => false,
                    'discriminatorClass'  => array(
                        'foo' => array(
                            'dataType'     => 'string',
                            'required'     => false,
                            'description'  => null,
                            'readonly'     => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        'bar' => array(
                            'dataType'     => 'string',
                            'required'     => false,
                            'description'  => null,
                            'readonly'     => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                        $discriminatorFieldName => array(
                            'dataType'     => 'string',
                            'required'     => true,
                            'description'  => 'type = TYPE_1',
                            'format'       => null,
                            'readonly'     => false,
                            'sinceVersion' => null,
                            'untilVersion' => null,
                        ),
                    ),
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
