<?php

namespace Nelmio\ApiDocBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

class FormCsrfProtectionTest extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        static::bootKernel();
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        return new TestKernel(TestKernel::USE_FORM_CSRF);
    }

    public function testTokenDescription()
    {
        $this->assertEquals([
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
}
