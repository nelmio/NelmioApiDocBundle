<?php
namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested;
use Nelmio\ApiDocBundle\Parser\JmsMetadataParser;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;

class JmsMetadataParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParserWithNestedType()
    {
        $metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');
        $docCommentExtractor = $this->getMockBuilder('Nelmio\ApiDocBundle\Util\DocCommentExtractor')
            ->disableOriginalConstructor()
            ->getMock();

        $propertyMetadataFoo = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'foo');
        $propertyMetadataFoo->type = 'DateTime';

        $propertyMetadataBar = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'bar');
        $propertyMetadataBar->type = array(
            'name' => 'string'
        );

        $propertyMetadataBaz = new PropertyMetadata('Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested', 'baz');
        $propertyMetadataBaz->type = array(
            'name' => 'array',
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

        $input = new JmsNested();

        $metadataFactory->expects($this->once())
            ->method('getMetadataForClass')
            ->with($input)
            ->will($this->returnValue($metadata));

        $jmsMetadataParser = new JmsMetadataParser($metadataFactory, $docCommentExtractor);

        $output = $jmsMetadataParser->parse($input);

        $this->assertEquals(array(
            'foo' => array(
                'dataType' => 'DateTime',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false
            ),
            'bar' => array(
                'dataType' => 'string',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false
            ),
            'baz' => array(
                'dataType' => 'array of integers',
                'required' => false,
                'description' => 'No description.',
                'readonly' => false
            )
        ), $output);
    }
}