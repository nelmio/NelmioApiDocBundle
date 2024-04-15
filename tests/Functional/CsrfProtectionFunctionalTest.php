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

use Symfony\Component\HttpKernel\KernelInterface;

class CsrfProtectionFunctionalTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    /**
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_FORM_CSRF);
    }

    public function testTokenPropertyExistsPerDefaultIfEnabledPerFrameworkConfig(): void
    {
        // Make sure that test precondition is correct.
        $isCsrfFormExtensionEnabled = self::getContainer()->getParameter('form.type_extension.csrf.enabled');
        self::assertTrue($isCsrfFormExtensionEnabled, 'The test needs the csrf form extension to be enabled.');

        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'quz' => [
                    '$ref' => '#/components/schemas/User',
                ],
                '_token' => [
                    'description' => 'CSRF token',
                    'type' => 'string',
                ],
            ],
            'required' => ['quz', '_token'],
            'schema' => 'FormWithModel',
        ], json_decode($this->getModel('FormWithModel')->toJson(), true));
    }

    public function testTokenPropertyExistsIfCsrfProtectionIsEnabled(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
                '_token' => [
                    'description' => 'CSRF token',
                    'type' => 'string',
                ],
            ],
            'required' => ['name', '_token'],
            'schema' => 'FormWithCsrfProtectionEnabledType',
        ], json_decode($this->getModel('FormWithCsrfProtectionEnabledType')->toJson(), true));
    }

    public function testTokenPropertyNotExistsIfCsrfProtectionIsDisabled(): void
    {
        self::assertEquals([
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['name'],
            'schema' => 'FormWithCsrfProtectionDisabledType',
        ], json_decode($this->getModel('FormWithCsrfProtectionDisabledType')->toJson(), true));
    }
}
