<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Model\NameGenerator;

use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\NameGenerator\GroupAppendingModelNameGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

final class GroupAppendingModelNameGeneratorTest extends TestCase
{
    private GroupAppendingModelNameGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new GroupAppendingModelNameGenerator();
    }

    /**
     * @return iterable<string, array{0: string, 1: string[], 2: string}>
     */
    public static function provideTestGenerateNameCases(): iterable
    {
        yield 'name without groups' => ['User', [], 'User'];
        yield 'name with simple groups' => ['User', ['api'], 'User_api'];
        yield 'name with groups containing special characters' => ['User', ['api-v1', 'admin_panel'], 'User_adminPanel_apiV1'];
        yield 'name with multiple simple groups sorted alphabetically' => ['Product', ['admin', 'api'], 'Product_admin_api'];
        yield 'name with groups containing spaces' => ['Product', ['public API', 'read-only'], 'Product_publicApi_readOnly'];
        yield 'name with single group containing multiple special characters' => ['Order', ['v2.0-beta'], 'Order_v20Beta'];
        yield 'name with common serialization groups' => ['Customer', ['Default', 'customer:read', 'customer:write'], 'Customer_customerRead_customerWrite_default'];
    }

    /**
     * @param string[] $groups
     */
    #[DataProvider('provideTestGenerateNameCases')]
    public function testGenerateNameCorrectlySanitizesAndJoinsGroupsWithName(string $name, array $groups, string $expectedName): void
    {
        $model = new Model(
            $this->createMock(Type::class),
            $groups
        );

        $result = $this->generator->generateName($model, $name, []);

        self::assertSame($expectedName, $result);
    }
}
