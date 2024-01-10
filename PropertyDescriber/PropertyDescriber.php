<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations as OA;

final class PropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var PropertyDescriberInterface[] Recursion helper */
    private $called = [];

    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;

    public function __construct(
        iterable $propertyDescribers
    ) {
        $this->propertyDescribers = $propertyDescribers;
    }

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = []): void
    {
        if (!$propertyDescriber = $this->getPropertyDescriber($types)) {
            return;
        }

        $this->called[] = $propertyDescriber;
        $propertyDescriber->describe($types, $property, $groups, $schema, $context);
        $this->called = []; // Reset recursion helper
    }

    public function supports(array $types): bool
    {
        return null !== $this->getPropertyDescriber($types);
    }

    private function getPropertyDescriber(array $types): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            /* BC layer for Symfony < 6.3 @see https://symfony.com/doc/6.3/service_container/tags.html#reference-tagged-services */
            if ($propertyDescriber instanceof self) {
                continue;
            }

            // Prevent infinite recursion
            if (in_array($propertyDescriber, $this->called, true)) {
                continue;
            }

            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }

            if ($propertyDescriber instanceof PropertyDescriberAwareInterface) {
                $propertyDescriber->setPropertyDescriber($this);
            }

            if ($propertyDescriber->supports($types)) {
                return $propertyDescriber;
            }
        }

        return null;
    }
}
