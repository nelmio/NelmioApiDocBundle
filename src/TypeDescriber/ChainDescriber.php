<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\TypeDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations\Schema;
use Symfony\Component\TypeInfo\Type;

/**
 * @implements TypeDescriberInterface<Type>
 *
 * @internal
 */
final class ChainDescriber implements TypeDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var iterable<TypeDescriberInterface> */
    private iterable $describers;

    /**
     * @param iterable<TypeDescriberInterface> $describers
     */
    public function __construct(
        iterable $describers
    ) {
        $this->describers = $describers;
    }

    public function describe(Type $type, Schema $schema, array $context = []): void
    {
        foreach ($this->describers as $describer) {
            if ($describer instanceof ModelRegistryAwareInterface) {
                $describer->setModelRegistry($this->modelRegistry);
            }

            if ($describer instanceof TypeDescriberAwareInterface) {
                $describer->setDescriber($this);
            }

            if ($describer->supports($type, $context)) {
                $describer->describe($type, $schema, $context);
            }
        }
    }

    public function supports(Type $type, array $context = []): bool
    {
        foreach ($this->describers as $describer) {
            if ($describer->supports($type, $context)) {
                return true;
            }
        }

        return false;
    }
}
