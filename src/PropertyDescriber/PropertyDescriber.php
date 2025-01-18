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

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use OpenApi\Annotations as OA;
use Symfony\Component\PropertyInfo\Type;

final class PropertyDescriber implements PropertyDescriberInterface, ModelRegistryAwareInterface
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
        if (null === $propertyDescriber = $this->getPropertyDescriber($types, $context)) {
            return;
        }

        $this->called[$this->getHash($types)][] = $propertyDescriber;
        $propertyDescriber->describe($types, $property, $context);
        $this->called = []; // Reset recursion helper
    }

    public function supports(array $types, array $context = []): bool
    {
        return null !== $this->getPropertyDescriber($types, $context);
    }

    /**
     * @param Type[] $types
     */
    private function getHash(array $types): string
    {
        return md5(serialize($types));
    }

    /**
     * @param Type[]               $types
     * @param array<string, mixed> $context
     */
    private function getPropertyDescriber(array $types, array $context): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            // Prevent infinite recursion
            if (\array_key_exists($this->getHash($types), $this->called)) {
                if (\in_array($propertyDescriber, $this->called[$this->getHash($types)], true)) {
                    continue;
                }
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
