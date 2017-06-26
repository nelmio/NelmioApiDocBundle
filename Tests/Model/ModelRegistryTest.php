<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Model;

use EXSyst\Component\Swagger\Schema;
use EXSyst\Component\Swagger\Swagger;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\Model\ModelRegistry;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Type;

class ModelRegistryTest extends TestCase
{
    /**
     * @dataProvider unsupportedTypesProvider
     */
    public function testUnsupportedTypeException(Type $type, string $stringType)
    {
        $this->expectException('\LogicException');
        $this->expectExceptionMessage(sprintf('Schema of type "%s" can\'t be generated, no describer supports it.', $stringType));

        $registry = new ModelRegistry([], new Swagger());
        $registry->register(new Model($type));
        $registry->registerDefinitions();
    }

    public function unsupportedTypesProvider()
    {
        return [
            [new Type(Type::BUILTIN_TYPE_ARRAY, false, null, true), 'mixed[]'],
            [new Type(Type::BUILTIN_TYPE_OBJECT, false, self::class), self::class],
        ];
    }
}
