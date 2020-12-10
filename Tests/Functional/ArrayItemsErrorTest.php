<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException;

class ArrayItemsErrorTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        static::createClient([], ['HTTP_HOST' => 'api.example.com']);
    }

    public function testModelPictureDocumentation()
    {
        $this->expectException(UndocumentedArrayItemsException::class);
        $this->expectExceptionMessage('Property "Nelmio\ApiDocBundle\Tests\Functional\Entity\ArrayItemsError\Bar::things[]" is an array, but its items type isn\'t specified.');

        $this->getOpenApiDefinition();
    }

    protected static function createKernel(array $options = [])
    {
        return new TestKernel(TestKernel::ERROR_ARRAY_ITEMS);
    }
}
