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
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\ArrayOfInt;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\ArrayOfString;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\ComplexArray;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\SimpleClass;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase;
use OpenApi\Annotations as OA;
use OpenApi\Annotations\OpenApi;
use Symfony\Component\PropertyInfo\Type as LegacyType;

class ObjectModelDescriberTest extends WebTestCase
{
    private ObjectModelDescriber $modelDescriber;

    protected function setUp(): void
    {
        self::bootKernel();

        $context = Util::createContext(['version' => '3.0.0']);
        $openApi = new OpenApi(['_context' => $context]);
        $this->modelDescriber = $this->getContainer()->get('nelmio_api_doc.model_describers.object');

        $modelRegistry = new ModelRegistry([$this->modelDescriber], $openApi);

        $this->modelDescriber->setModelRegistry($modelRegistry);
    }

    /**
     * @dataProvider provideFixtures
     */
    public function testItDescribes(string $class): void
    {
        $model = new Model(new LegacyType('object', false, $class));
        $schema = new OA\Schema([
            'type' => 'object',
        ]);

        $this->modelDescriber->describe($model, $schema);

        $reflect = new \ReflectionClass($class);

        if (!file_exists($fixtureDir = dirname($reflect->getFileName()).'/'.$reflect->getShortName().'.json')) {
            file_put_contents($fixtureDir, $schema->toJson());
        }

        self::assertSame(
            self::getFixture($fixtureDir),
            $schema->toJson(),
        );
    }

    public static function provideFixtures(): \Generator
    {
        //        yield [
        //            SimpleClass::class,
        //        ];

        yield [
            ArrayOfInt::class,
        ];

        //        yield [
        //            ArrayOfString::class,
        //        ];
        //
        //        yield [
        //            ComplexArray::class
        //        ];
    }

    private static function getFixture(string $fixture): string
    {
        if (!file_exists($fixture)) {
            self::fail(sprintf('The fixture file "%s" does not exist.', $fixture));
        }

        $content = file_get_contents($fixture);

        if (false === $content) {
            self::fail(sprintf('Failed to read the fixture file "%s".', $fixture));
        }

        return $content;
    }
}
