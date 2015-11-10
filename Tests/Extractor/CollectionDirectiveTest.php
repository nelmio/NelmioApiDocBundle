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

class CollectionDirectiveTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TestExtractor
     */
    private $testExtractor;

    public function setUp()
    {
        $this->testExtractor = new TestExtractor();
    }

    private function normalize($input)
    {
        return $this->testExtractor->getNormalization($input);
    }

    /**
     * @dataProvider dataNormalizationTests
     */
    public function testNormalizations($input, callable $callable)
    {
          call_user_func($callable, $this->normalize($input), $this);
    }

    public function dataNormalizationTests()
    {
        return array(
            'test_simple_notation' => array(
                'array<User>',
                function ($actual, \PHPUnit_Framework_TestCase $case) {
                    $case->assertArrayHasKey('collection', $actual);
                    $case->assertArrayHasKey('collectionName', $actual);
                    $case->assertArrayHasKey('class', $actual);

                    $case->assertTrue($actual['collection']);
                    $case->assertEquals('', $actual['collectionName']);
                    $case->assertEquals('User', $actual['class']);
                }
            ),
            'test_simple_notation_with_namespaces' => array(
                'array<Vendor0_2\\_Namespace1\\Namespace_2\\User>',
                function ($actual, \PHPUnit_Framework_TestCase $case) {
                    $case->assertArrayHasKey('collection', $actual);
                    $case->assertArrayHasKey('collectionName', $actual);
                    $case->assertArrayHasKey('class', $actual);

                    $case->assertTrue($actual['collection']);
                    $case->assertEquals('', $actual['collectionName']);
                    $case->assertEquals('Vendor0_2\\_Namespace1\\Namespace_2\\User', $actual['class']);
                }
            ),
            'test_simple_named_collections' => array(
                'array<Group> as groups',
                function ($actual, \PHPUnit_Framework_TestCase $case) {
                    $case->assertArrayHasKey('collection', $actual);
                    $case->assertArrayHasKey('collectionName', $actual);
                    $case->assertArrayHasKey('class', $actual);

                    $case->assertTrue($actual['collection']);
                    $case->assertEquals('groups', $actual['collectionName']);
                    $case->assertEquals('Group', $actual['class']);
                }
            ),
            'test_namespaced_named_collections' => array(
                'array<_Vendor\\Namespace0\\Namespace_2F3\\Group> as groups',
                function ($actual, \PHPUnit_Framework_TestCase $case) {
                    $case->assertArrayHasKey('collection', $actual);
                    $case->assertArrayHasKey('collectionName', $actual);
                    $case->assertArrayHasKey('class', $actual);

                    $case->assertTrue($actual['collection']);
                    $case->assertEquals('groups', $actual['collectionName']);
                    $case->assertEquals('_Vendor\\Namespace0\\Namespace_2F3\\Group', $actual['class']);
                }
            ),

        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @dataProvider dataInvalidDirectives
     * @param $input
     */
    public function testInvalidDirectives($input)
    {
        $this->normalize($input);
    }

    public function dataInvalidDirectives()
    {
        return array(
            array('array<>'),
            array('array<Vendor\\>'),
            array('array<2Vendor\\>'),
            array('array<Vendor\\2Class>'),
            array('array<User> as'),
            array('array<User> as '),
        );
    }
}
