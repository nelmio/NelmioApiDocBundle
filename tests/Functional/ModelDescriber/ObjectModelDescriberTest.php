<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use Nelmio\ApiDocBundle\ModelDescriber\ObjectModelDescriber;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PropertyInfo\Type as LegacyType;

class ObjectModelDescriberTest extends WebTestCase
{
    protected ObjectModelDescriber $modelDescriber;

    protected function setUp(): void
    {
        self::bootKernel();

        $context = Util::createContext(['version' => '3.0.0']);
        $openApi = new OpenApi(['_context' => $context]);
        $this->modelDescriber = self::getContainer()->get('nelmio_api_doc.model_describers.object');

        $modelRegistry = new ModelRegistry([$this->modelDescriber], $openApi);

        $this->modelDescriber->setModelRegistry($modelRegistry);
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testItDescribes(string $class, string $fixtureDir): void
    {
        $model = new Model(new LegacyType('object', false, $class));
        $schema = new OA\Schema([
            'type' => 'object',
        ]);

        try {
            $this->modelDescriber->describe($model, $schema);
        } catch (\Exception $e) {
            self::markTestIncomplete($e->getMessage());
        }

        if (!file_exists($fixtureDir)) {
            file_put_contents($fixtureDir, $schema->toJson());
        }

        self::assertSame(
            self::getFixture($fixtureDir),
            $schema->toJson(),
        );
    }

    public static function provideFixtures(): \Generator
    {
        $finder = new Finder();
        $entityFiles = $finder->files()
            ->in(__DIR__.'/Fixtures')
            ->name('*.php')
            ->sortByCaseInsensitiveName();

        foreach ($entityFiles as $file) {
            $namespacedPath = str_replace(__DIR__.'/Fixtures', 'Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures', $file->getPathname());
            $pathWithBackslashes = str_replace('/', '\\', $namespacedPath);

            /** @var class-string $fullyQualifiedClassName */
            $fullyQualifiedClassName = str_replace('.php', '', $pathWithBackslashes);

            try {
                $classExists = class_exists($fullyQualifiedClassName);
            } catch (\Throwable) {
                // Skip classes that cannot be loaded (Unsupported syntax, etc.)
                continue;
            }

            if (!$classExists) {
                self::markTestIncomplete(\sprintf('The class "%s" does not exist.', $fullyQualifiedClassName));
            }

            yield [
                $fullyQualifiedClassName,
                str_replace('.php', '.json', $file->getPathname()),
            ];
        }
    }

    protected static function getFixture(string $fixture): string
    {
        if (!file_exists($fixture)) {
            self::fail(\sprintf('The fixture file "%s" does not exist.', $fixture));
        }

        $content = file_get_contents($fixture);

        if (false === $content) {
            self::fail(\sprintf('Failed to read the fixture file "%s".', $fixture));
        }

        return $content;
    }
}
