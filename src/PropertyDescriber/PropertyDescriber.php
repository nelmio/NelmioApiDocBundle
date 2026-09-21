<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;
use Symfony\Contracts\Service\ResetInterface;

final class PropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface, ResetInterface
{
    use ModelRegistryAwareTrait;

    /** @var array<string, PropertyDescriberInterface[]> Recursion helper */
    private array $called = [];

    /** @var iterable<PropertyDescriberInterface> */
    private iterable $propertyDescribers;

    /**
     * @param iterable<PropertyDescriberInterface> $propertyDescribers
     */
    public function __construct(
        iterable $propertyDescribers,
    ) {
        $this->propertyDescribers = $propertyDescribers;
    }

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, array $context = []): void
    {
        $scope = $this->getScope($types, $property);

        if (null === $propertyDescriber = $this->getPropertyDescriber($types, $context, $scope)) {
            return;
        }

        $this->called[$scope][] = $propertyDescriber;
        try {
            $propertyDescriber->describe($types, $property, $context);
        } finally {
            // Leave no recursion state behind, so a failed description cannot leak across requests
            // (FrankenPHP worker / long-lived processes).
            array_pop($this->called[$scope]);
            if ([] === $this->called[$scope]) {
                unset($this->called[$scope]);
            }
        }
    }

    public function reset(): void
    {
        $this->called = [];
    }

    public function supports(array $types, array $context = []): bool
    {
        return null !== $this->getPropertyDescriber($types, $context, null);
    }

    /**
     * A describer delegating back for the same types is only recursing while it does so for the
     * same property. The same types on another property, such as the one a nested model of the
     * very same class has, are a description of their own and start from the whole chain again.
     *
     * @param Type[] $types
     */
    private function getScope(array $types, OA\Schema $property): string
    {
        return spl_object_id($property).':'.md5(serialize($types));
    }

    /**
     * @param Type[]               $types
     * @param array<string, mixed> $context
     */
    private function getPropertyDescriber(array $types, array $context, ?string $scope): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            // Prevent infinite recursion
            if (null !== $scope && \in_array($propertyDescriber, $this->called[$scope] ?? [], true)) {
                continue;
            }

            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }

            if ($propertyDescriber instanceof PropertyDescriberAwareInterface) {
                $propertyDescriber->setPropertyDescriber($this);
            }

            if ($propertyDescriber->supports($types, $context)) {
                return $propertyDescriber;
            }
        }

        return null;
    }
}
