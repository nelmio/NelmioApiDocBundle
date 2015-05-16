<?php

namespace NelmioApiDocBundle\Tests\Parser;

use Nelmio\ApiDocBundle\Parser\OutputArrayParser;
use Nelmio\ApiDocBundle\Tests\WebTestCase;

class OutputArrayParserTest extends WebTestCase
{

    /**
     * @var OutputArrayParser
     */
    private $objectUnderTest;

    private $parseClassDefinition = array('class' => 'array<Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test>');

    protected function setUp()
    {
        parent::setUp();

        $this->objectUnderTest = new OutputArrayParser($this->mockService());
    }

    public function tearDown()
    {
        unset($this->objectUnderTest);
    }

    public function testReturnsTrueWhenClassAnnotationIsSupported()
    {
        $this->assertTrue($this->objectUnderTest->supports($this->parseClassDefinition));
    }

    public function testReturnsArrayWhenClassAnnotationHasArray()
    {
        $result = $this->objectUnderTest->parse($this->parseClassDefinition);
        $this->assertArrayHasKey('[]', $result);
        $this->assertArrayHasKey('dataType', $result['[]']);
        $this->assertEquals('array', $result['[]']['dataType']);
        $this->assertEquals('array of objects (Test)', $result['[]']['description']);
    }

    public function testReturnsFalseWhenClassAnnotationHasNoArray()
    {
        $result = $this->objectUnderTest->parse(
            array('class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test')
        );

        $this->assertFalse($result);
    }

    /**
     * @return mixed
     */
    private function mockService()
    {
        $mockedService = $this->getMockBuilder('Nelmio\ApiDocBundle\Parser\JmsMetadataParser')
            ->disableOriginalConstructor()
            ->getMock();

        $mockMethods = array('supports', 'parse');

        foreach ($mockMethods as $mockMethod) {

            $mockedService->expects($this->any())
                ->method($mockMethod)
                ->will($this->returnValue(true));
        }

        return $mockedService;
    }

}
