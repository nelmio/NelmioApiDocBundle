<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations as OA;

final class PropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    /** @var PropertyDescriberInterface[] */
    private $propertyDescribers;

    public function __construct(
        iterable $propertyDescribers
    ) {
        $this->propertyDescribers = $propertyDescribers;
    }

    public function describe(array $types, OA\Schema $property, array $groups = null, ?OA\Schema $schema = null, array $context = []): void
    {
        if (!$normalizer = $this->getPropertyDescriber($types, $context)) {
            return;
        }

        $normalizer->describe($types, $property, $groups, $schema, $context);
    }

    public function supports(array $types, array $context = []): bool
    {
        return null !== $this->getPropertyDescriber($types, $context);
    }

    private function getPropertyDescriber(array $types, array $context): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
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
