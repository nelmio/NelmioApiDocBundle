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
            throw new \LogicException(sprintf('No property describer supports the given type "%s".', implode(', ', $types)));
        }

        $normalizer->describe($types, $property, $groups, $schema, $context);
    }

    public function supports(array $types): bool
    {
        return null !== $this->getPropertyDescriber($types);
    }

    private function getPropertyDescriber(array $types): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            if ($propertyDescriber instanceof ModelRegistryAwareInterface) {
                $propertyDescriber->setModelRegistry($this->modelRegistry);
            }

            if ($propertyDescriber->supports($types)) {
                return $propertyDescriber;
            }
        }

        return null;
    }
}
