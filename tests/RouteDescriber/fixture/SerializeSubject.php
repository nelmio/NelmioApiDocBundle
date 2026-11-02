<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\RouteDescriber\fixture;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;

final class SerializeSubject
{
    #[Serialize]
    public function object(): \stdClass
    {
        return new \stdClass();
    }

    #[Serialize(code: 201)]
    public function created(): \stdClass
    {
        return new \stdClass();
    }

    #[Serialize(context: ['groups' => ['read']])]
    public function withContext(): \stdClass
    {
        return new \stdClass();
    }

    #[Serialize(headers: ['X-Foo' => 'bar', 'Content-Type' => 'text/plain'])]
    public function withHeaders(): \stdClass
    {
        return new \stdClass();
    }

    #[Serialize]
    public function nothing(): void
    {
    }

    #[Serialize]
    public function response(): Response
    {
        return new Response();
    }

    public function withoutAttribute(): \stdClass
    {
        return new \stdClass();
    }
}
