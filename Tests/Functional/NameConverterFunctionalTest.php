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

use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;

class NameConverterFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        $nameConverter = new class() implements AdvancedNameConverterInterface {
            public function normalize(string $propertyName, string $class = null, string $format = null, array $context = []): string
            {
                if (!isset($context['secret_name_converter_value'])) {
                    return $propertyName;
                }

                return 'name_converter_context_' . $propertyName;
            }

            public function denormalize(string $propertyName, string $class = null, string $format = null, array $context = []): string
            {
                throw new \RuntimeException('Was not expected to be called');
            }
        };

        self::getContainer()->set('serializer.name_converter.metadata_aware', $nameConverter);

        parent::setUp();
    }

    public function testContextPassedToNameConverter()
    {
        $this->getOperation('/api/name_converter_context', 'get');

        $model = $this->getModel('EntityThroughNameConverter');
        $this->assertCount(2, $model->properties);
        $this->assertNotHasProperty('id', $model);
        $this->assertHasProperty('name_converter_context_id', $model);
        $this->assertNotHasProperty('name', $model);
        $this->assertHasProperty('name_converter_context_name', $model);
    }
}
