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
use Nelmio\ApiDocBundle\Describer\ApiPlatformDescriber;
use OpenApi\Annotations\OpenApi;
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

        $expectedApi = new OpenApi(['info' => ['title' => 'My Test App']]);
        $this->assertEquals($expectedApi->toJson(), $this->getOpenApiDoc()->toJson());
    }

    public function testDescribeRemovesBasePathAfterNormalization()
    {
        $this->normalizer->expects($this->once())
            ->method('normalize')
            ->with($this->documentation)
            ->willReturn(['info' => ['title' => 'My Test App'], 'basePath' => '/foo']);

        $expectedApi = new OpenApi(['info' => ['title' => 'My Test App']]);
        $this->assertEquals($expectedApi->toJson(), $this->getOpenApiDoc()->toJson());
    }

    protected function setUp(): void
    {
        $this->documentation = new Documentation(new ResourceNameCollection(['dummy' => 'dummy']));

        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->expects($this->once())
            ->method('supportsNormalization')
            ->willReturn(true);

        $this->describer = new ApiPlatformDescriber($this->documentation, $this->normalizer);
    }
}
