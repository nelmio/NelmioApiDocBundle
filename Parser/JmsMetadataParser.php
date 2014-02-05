<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Parser;

use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

/**
 * Uses the JMS metadata factory to extract input/output model information
 */
class JmsMetadataParser implements ParserInterface
{
    /**
     * @var \Metadata\MetadataFactoryInterface
     */
    private $factory;

    /**
     * @var PropertyNamingStrategyInterface
     */
    private $namingStrategy;

    /**
     * @var \Nelmio\ApiDocBundle\Util\DocCommentExtractor
     */
    private $commentExtractor;

    /**
     * Constructor, requires JMS Metadata factory
     */
    public function __construct(
        MetadataFactoryInterface $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        DocCommentExtractor $commentExtractor
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $input)
    {
        $className = $input['class'];

        try {
            if ($meta = $this->factory->getMetadataForClass($className)) {
                return true;
            }
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input)
    {
        $className = $input['class'];
        $groups    = $input['groups'];

        return $this->doParse($className, array(), $groups);
    }

    /**
     * Recursively parse all metadata for a class
     *
     * @param  string                    $className Class to get all metadata for
     * @param  array                     $visited   Classes we've already visited to prevent infinite recursion.
     * @param  array                     $groups    Serialization groups to include.
     * @return array                     metadata for given class
     * @throws \InvalidArgumentException
     */
    protected function doParse($className, $visited = array(), array $groups = array())
    {
        $meta = $this->factory->getMetadataForClass($className);

        if (null === $meta) {
            throw new \InvalidArgumentException(sprintf("No metadata found for class %s", $className));
        }

        $exclusionStrategies   = array();
        if ($groups) {
            $exclusionStrategies[] = new GroupsExclusionStrategy($groups);
        }

        $params = array();

        // iterate over property metadata
        foreach ($meta->propertyMetadata as $item) {
            if (!is_null($item->type)) {
                $name = $this->namingStrategy->translateName($item);

                $dataType = $this->processDataType($item);

                // apply exclusion strategies
                foreach ($exclusionStrategies as $strategy) {
                    if (true === $strategy->shouldSkipProperty($item, SerializationContext::create())) {
                        continue 2;
                    }
                }

                $params[$name] = array(
                    'dataType'     => $dataType['normalized'],
                    'required'     => false,
                    //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                    'description'  => $this->getDescription($item),
                    'readonly'     => $item->readOnly,
                    'sinceVersion' => $item->sinceVersion,
                    'untilVersion' => $item->untilVersion,
                );

                if (!is_null($dataType['class'])) {
                    $params[$name]['class'] = $dataType['class'];
                }

                // if class already parsed, continue, to avoid infinite recursion
                if (in_array($dataType['class'], $visited)) {
                    continue;
                }

                // check for nested classes with JMS metadata
                if ($dataType['class'] && null !== $this->factory->getMetadataForClass($dataType['class'])) {
                    $visited[]                 = $dataType['class'];
                    $params[$name]['children'] = $this->doParse($dataType['class'], $visited, $groups);
                }
            }
        }

        return $params;
    }

    /**
     * Figure out a normalized data type (for documentation), and get a
     * nested class name, if available.
     *
     * @param  PropertyMetadata $type
     * @return array
     */
    protected function processDataType(PropertyMetadata $item)
    {
        // check for a type inside something that could be treated as an array
        if ($nestedType = $this->getNestedTypeInArray($item)) {
            if ($this->isPrimitive($nestedType)) {
                return array(
                    'normalized' => sprintf("array of %ss", $nestedType),
                    'class' => null
                );
            }

            $exp = explode("\\", $nestedType);

            return array(
                'normalized' => sprintf("array of objects (%s)", end($exp)),
                'class' => $nestedType
            );
        }

        $type = $item->type['name'];

        // could be basic type
        if ($this->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'class' => null
            );
        }

        // we can use type property also for custom handlers, then we don't have here real class name
        if (!class_exists($type)) {
            return array(
                'normalized' => sprintf("custom handler result for (%s)", $type),
                'class' => null
            );
        }

        // if we got this far, it's a general class name
        $exp = explode("\\", $type);

        return array(
            'normalized' => sprintf("object (%s)", end($exp)),
            'class' => $type
        );
    }

    protected function isPrimitive($type)
    {
        return in_array($type, array('boolean', 'integer', 'string', 'float', 'double', 'array', 'DateTime'));
    }

    /**
     * Check the various ways JMS describes values in arrays, and
     * get the value type in the array
     *
     * @param  PropertyMetadata $item
     * @return string|null
     */
    protected function getNestedTypeInArray(PropertyMetadata $item)
    {
        if (isset($item->type['name']) && in_array($item->type['name'], array('array', 'ArrayCollection'))) {
            if (isset($item->type['params'][1]['name'])) {
                // E.g. array<string, MyNamespaceMyObject>
                return $item->type['params'][1]['name'];
            }
            if (isset($item->type['params'][0]['name'])) {
                // E.g. array<MyNamespaceMyObject>
                return $item->type['params'][0]['name'];
            }
        }

        return null;
    }

    protected function getDescription(PropertyMetadata $item)
    {
        $ref = new \ReflectionClass($item->class);
        if ($item instanceof VirtualPropertyMetadata) {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getMethod($item->getter));
        } else {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getProperty($item->name));
        }

        return $extracted;
    }
}
