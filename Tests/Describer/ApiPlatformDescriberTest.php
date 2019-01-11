<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use ApiPlatform\Core\Documentation\Documentation;
use ApiPlatform\Core\Metadata\Resource\ResourceNameCollection;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Describer\ApiPlatformDescriber;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ApiPlatformDescriberTest extends AbstractDescriberTest
{
    private $documentation;

    private $normalizer;

    public function testDescribe()
    {
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($this->documentation)
            ->willReturn(['info' => ['title' => 'My Test App']]);

        $expectedApi = new Swagger(['info' => ['title' => 'My Test App']]);
        $this->assertEquals($expectedApi->toArray(), $this->getSwaggerDoc()->toArray());
    }

    public function testDescribeRemovesBasePathAfterNormalization()
    {
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($this->documentation)
            ->willReturn(['info' => ['title' => 'My Test App'], 'basePath' => '/foo']);

        $expectedApi = new Swagger(['info' => ['title' => 'My Test App']]);
        $this->assertEquals($expectedApi->toArray(), $this->getSwaggerDoc()->toArray());
    }

    protected function setUp()
    {
        $this->documentation = new Documentation(new ResourceNameCollection(['dummy' => 'dummy']));

        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->expects($this->once())
            ->method('supportsNormalization')
            ->willReturn(true);

        $this->describer = new ApiPlatformDescriber($this->documentation, $this->normalizer);
    }
}
