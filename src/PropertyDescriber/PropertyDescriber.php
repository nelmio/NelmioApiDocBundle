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
        iterable $propertyDescribers
    ) {
        $this->propertyDescribers = $propertyDescribers;
    }

    /**
     * @param array<string, mixed> $context Context options for describing the property
     */
    public function describe(array $types, OA\Schema $property, ?array $groups = null, ?OA\Schema $schema = null, array $context = []): void
    {
        if (null === $schema) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.15.0',
                '"%s()" will have a new "OA\Schema $schema" argument in a future version. Not defining it or passing null is deprecated',
                __METHOD__
            );
        }

        if (null !== $groups) {
            trigger_deprecation(
                'nelmio/api-doc-bundle',
                '4.17.0',
                'Using the $groups parameter of "%s()" is deprecated and will be removed in a future version. Pass groups via $context[\'groups\']',
                __METHOD__
            );
        }

        if (null === $propertyDescriber = $this->getPropertyDescriber($types)) {
            return;
        }

        $this->called[$this->getHash($types)][] = $propertyDescriber;
        $propertyDescriber->describe($types, $property, $groups, $schema, $context);
        $this->called = []; // Reset recursion helper
    }

    public function supports(array $types): bool
    {
        return null !== $this->getPropertyDescriber($types);
    }

    /**
     * @param Type[] $types
     */
    private function getHash(array $types): string
    {
        return md5(serialize($types));
    }

    /**
     * @param Type[] $types
     */
    private function getPropertyDescriber(array $types): ?PropertyDescriberInterface
    {
        foreach ($this->propertyDescribers as $propertyDescriber) {
            /* BC layer for Symfony < 6.3 @see https://symfony.com/doc/6.3/service_container/tags.html#reference-tagged-services */
            if ($propertyDescriber instanceof self) {
                continue;
            }

            // Prevent infinite recursion
            if (key_exists($this->getHash($types), $this->called)) {
                if (in_array($propertyDescriber, $this->called[$this->getHash($types)], true)) {
                    continue;
                }
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
