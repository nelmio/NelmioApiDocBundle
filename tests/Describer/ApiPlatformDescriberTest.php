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

use ApiPlatform\Documentation\Documentation;
use ApiPlatform\Metadata\Resource\ResourceNameCollection;
use Nelmio\ApiDocBundle\Describer\ApiPlatformDescriber;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ApiPlatformDescriberTest extends AbstractDescriberTestCase
{
    private Documentation $documentation;

    /**
     * @var MockObject&NormalizerInterface
     */
    private NormalizerInterface $normalizer;

    public function testDescribe(): void
    {
        $this->normalizer->expects(self::once())
            ->method('normalize')
            ->with($this->documentation)
            ->willReturn(['info' => ['title' => 'My Test App']]);

        $expectedApi = new OpenApi(['info' => ['title' => 'My Test App'], '_context' => new Context()]);
        self::assertEquals($expectedApi->toJson(), $this->getOpenApiDoc()->toJson());
    }

    public function testDescribeRemovesBasePathAfterNormalization(): void
    {
        $this->normalizer->expects(self::once())
            ->method('normalize')
            ->with($this->documentation)
            ->willReturn(['info' => ['title' => 'My Test App'], 'basePath' => '/foo']);

        $expectedApi = new OpenApi(['info' => ['title' => 'My Test App'], '_context' => new Context()]);
        self::assertEquals($expectedApi->toJson(), $this->getOpenApiDoc()->toJson());
    }

    protected function setUp(): void
    {
        $this->documentation = new Documentation(new ResourceNameCollection(['dummy' => 'dummy']));

        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->normalizer->expects(self::once())
            ->method('supportsNormalization')
            ->willReturn(true);

        $this->describer = new ApiPlatformDescriber($this->documentation, $this->normalizer);
    }
}
