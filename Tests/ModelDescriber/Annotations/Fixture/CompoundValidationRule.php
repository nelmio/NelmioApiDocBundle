<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations\Fixture;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Compound;

if (!class_exists(Compound::class)) {
    class_alias(CompoundStub::class, Compound::class);
}

/**
 * @Annotation
 */
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
