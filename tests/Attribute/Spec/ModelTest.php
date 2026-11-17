<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Attribute\Spec;

use Nelmio\ApiDocBundle\Attribute\Spec\Model;
use Nelmio\ApiDocBundle\Describer\SpecAttributeDescriber;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\Article;
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        if (!SpecAttributeDescriber::isSupported()) {
            self::markTestSkipped('Spec attributes need swagger-php 6.17 or later.');
        }
    }

    public function testGetModel(): void
    {
        $model = new Model(
            type: Article::class,
            groups: ['light'],
            options: ['foo' => 'bar'],
            serializationContext: ['enable_max_depth' => true],
            name: 'LightArticle',
            description: 'An article',
        );

        self::assertSame([
            'type' => Article::class,
            'groups' => ['light'],
            'options' => ['foo' => 'bar'],
            'serializationContext' => ['enable_max_depth' => true],
            'name' => 'LightArticle',
        ], $model->getModel());
        self::assertSame('An article', $model->description);
    }

    public function testDefaults(): void
    {
        self::assertSame([
            'type' => Article::class,
            'groups' => null,
            'options' => [],
            'serializationContext' => [],
            'name' => null,
        ], (new Model(Article::class))->getModel());
    }
}
