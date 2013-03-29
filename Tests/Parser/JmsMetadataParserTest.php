<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested;
use Nelmio\ApiDocBundle\Parser\JmsMetadataParser;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

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

        $input = new JmsNested();

        $metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with($input)
            ->will($this->returnValue($metadata));

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);

        $output = $jmsMetadataParser->parse($input);

        $this->assertEquals(array(
            'foo' => array(
                'dataType' => 'DateTime',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false,
                'groups' => null,
            ),
            'bar' => array(
                'dataType' => 'string',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false,
                'groups' => null,
            ),
            'baz' => array(
                'dataType' => 'array of integers',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false,
                'groups' => null,
            )
        ), $output);
    }

    public function dataTestParserWithNestedType()
    {
        return array(
            array('array'),
            array('ArrayCollection')
        );
    }
}
