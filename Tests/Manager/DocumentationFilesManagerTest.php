<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Tests\Manager;

use Nelmio\ApiDocBundle\Manager\DocumentationFilesManager;

/**
 * Class DocumentationFilesManagerTest
 */
class DocumentationFilesManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $apiDocFactory;

    /**
     * @var DocumentationFilesManager
     */
    protected $documentationFilesManager;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->apiDocFactory = $this->getMockBuilder('Nelmio\ApiDocBundle\Factory\ApiDocFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $this->documentationFilesManager = new DocumentationFilesManager($this->apiDocFactory);
    }

    /**
     * Test exception if file doesn't exist
     *
     * @param array $provider
     *
     * @expectedException Nelmio\ApiDocBundle\Exception\FileNotFoundException
     *
     * @dataProvider exceptionProvider
     */
    public function testParseWithInvalidFile($provider)
    {
        $this->documentationFilesManager->parse($provider);
    }


    /**
     * Test parse with valid file
     */
    public function testParse()
    {
        $files = array(__DIR__ . '/testFile.yml');

        $this->apiDocFactory
            ->expects($this->any())
            ->method('create')
            ->willReturn(true);

        $this->assertEquals(array(true), $this->documentationFilesManager->parse($files));
    }

    /**
     * @return array
     */
    public function exceptionProvider()
    {
        $basePath = __DIR__;

        return array(
            array(
                array(
                    $basePath . '/testNonExistFile.yml'
                )
            ),
            array(
                array(
                    $basePath . '/testFile.yml',
                    $basePath . '/testNonExistFile.yml',
                )
            ),
            array(
                array(
                    $basePath . '/testNonExistFile.yml',
                    $basePath . '/testFile.yml',
                )
            )
        );
    }
}
