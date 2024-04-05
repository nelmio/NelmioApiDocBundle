<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

if (!class_exists(Compound::class)) {
    class_alias(CompoundStub::class, Compound::class);
}

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class CompoundValidationRule extends Compound
{
    protected function getConstraints(array $options): array
    {
        return [
            new Assert\Type('numeric'),
            new Assert\NotBlank(),
            new Assert\Positive(),
            new Assert\LessThan(5),
        ];
    }
}
