<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber;

use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\RouteDescriber\SymfonySerializeDescriber;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\fixture\SerializeSubject;
use Nelmio\ApiDocBundle\Tests\RouteDescriber\fixture\SpyTypeDescriber;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Route;

class SymfonySerializeDescriberTest extends TestCase
{
    private SpyTypeDescriber $typeDescriber;

    protected function setUp(): void
    {
        if (!class_exists(Serialize::class)) {
            self::markTestSkipped('Requires Symfony 8.1');
        }

        $this->typeDescriber = new SpyTypeDescriber();
    }

    public function testObjectReturnTypeIsDescribedAsJsonContent(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'object');

        $operation = $api->paths[0]->get;
        self::assertCount(1, $operation->responses);
        self::assertSame('200', $operation->responses[0]->response);
        self::assertSame('', $operation->responses[0]->description);
        self::assertSame('application/json', $operation->responses[0]->content[0]->mediaType);
        self::assertSame('#/components/schemas/Stub', $operation->responses[0]->content[0]->schema->ref);
    }

    public function testExistingIntKeyedResponseIsReused(): void
    {
        $api = new OA\OpenApi([]);
        $operation = Util::getOperation(Util::getPath($api, '/foo'), 'get');
        $existing = Util::getIndexedCollectionItem($operation, OA\Response::class, 200);
        $existing->description = 'Already documented';

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'object');

        self::assertCount(1, $operation->responses);
        self::assertSame('Already documented', $operation->responses[0]->description);
        self::assertSame('#/components/schemas/Stub', $operation->responses[0]->content[0]->schema->ref);
    }

    public function testExistingStringKeyedResponseIsReused(): void
    {
        $api = new OA\OpenApi([]);
        $operation = Util::getOperation(Util::getPath($api, '/foo'), 'get');
        $existing = Util::getIndexedCollectionItem($operation, OA\Response::class, '200');
        $existing->description = 'Already documented';

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'object');

        self::assertCount(1, $operation->responses);
        self::assertSame('Already documented', $operation->responses[0]->description);
    }

    public function testExistingContentIsNotOverwritten(): void
    {
        $api = new OA\OpenApi([]);
        $operation = Util::getOperation(Util::getPath($api, '/foo'), 'get');
        $existing = Util::getIndexedCollectionItem($operation, OA\Response::class, 200);
        $existing->description = '';
        Util::getChild(
            Util::getIndexedCollectionItem($existing, OA\MediaType::class, 'application/json'),
            OA\Schema::class
        )->ref = '#/components/schemas/Mine';

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'object');

        self::assertCount(1, $existing->content);
        self::assertSame('#/components/schemas/Mine', $existing->content[0]->schema->ref);
        self::assertSame([], $this->typeDescriber->calls);
    }

    public function testCodeIsUsedAsResponseKey(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['POST']), 'created');

        self::assertSame('201', $api->paths[0]->post->responses[0]->response);
    }

    public function testVoidReturnTypeDocumentsTheStatusCodeOnly(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'nothing');

        $response = $api->paths[0]->get->responses[0];
        self::assertSame('200', $response->response);
        self::assertSame(Generator::UNDEFINED, $response->content);
    }

    public function testResponseReturnTypeIsIgnored(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'response');

        self::assertSame(Generator::UNDEFINED, $api->paths);
    }

    public function testMethodWithoutTheAttributeIsIgnored(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'withoutAttribute');

        self::assertSame(Generator::UNDEFINED, $api->paths);
    }

    public function testHeadersAreDescribedWithoutContentType(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'withHeaders');

        $headers = $api->paths[0]->get->responses[0]->headers;
        self::assertCount(1, $headers);
        self::assertSame('X-Foo', $headers[0]->header);
        self::assertTrue($headers[0]->required);
        self::assertSame('string', $headers[0]->schema->type);
    }

    public function testSerializationContextIsForwardedToTheTypeDescriber(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET']), 'withContext');

        self::assertCount(1, $this->typeDescriber->calls);
        self::assertSame(['groups' => ['read']], $this->typeDescriber->calls[0]['context']);
    }

    public function testResponseIsAddedToEveryOperationOfTheRoute(): void
    {
        $api = new OA\OpenApi([]);

        $this->describe($api, new Route('/foo', [], [], [], null, [], ['GET', 'POST']), 'object');

        self::assertSame('200', $api->paths[0]->get->responses[0]->response);
        self::assertSame('200', $api->paths[0]->post->responses[0]->response);
    }

    private function describe(OA\OpenApi $api, Route $route, string $method): void
    {
        $describer = new SymfonySerializeDescriber(['json'], $this->typeDescriber);
        $describer->setModelRegistry(new ModelRegistry(
            [$this->createMock(ModelDescriberInterface::class)],
            $api,
        ));

        $describer->describe($api, $route, new \ReflectionMethod(SerializeSubject::class, $method));
    }
}
