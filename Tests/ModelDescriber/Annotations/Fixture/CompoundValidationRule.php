<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture;

use Attribute;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

if (!class_exists(Compound::class)) {
    class_alias(CompoundStub::class, Compound::class);
}

/**
 * @Annotation
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
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
