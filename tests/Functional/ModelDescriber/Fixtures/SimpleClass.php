<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures;

class SimpleClass
{
    public string $name;
    public int $age;
    public ?string $description;
    public ?int $height;
    public ?string $email;
    public ?string $phone;
    public ?string $address;
    public ?string $city;
}
