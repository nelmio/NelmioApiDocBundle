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
use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;

/**
 * Uses the JMS metadata factory to extract input/output model information
 */
class JmsMetadataParser implements ParserInterface, PostParserInterface
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

    private $typeMap = array(
        'integer' => DataTypes::INTEGER,
        'boolean' => DataTypes::BOOLEAN,
        'string' => DataTypes::STRING,
        'float' => DataTypes::FLOAT,
        'double' => DataTypes::FLOAT,
        'array' => DataTypes::COLLECTION,
        'DateTime' => DataTypes::DATETIME,
    );
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

        $reflection = new \ReflectionClass($className);
        $defaultProperties = $reflection->getDefaultProperties();

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

                if (!$dataType['inline']) {
                    $params[$name] = array(
                        'dataType'     => $dataType['normalized'],
                        'actualType'   => $dataType['actualType'],
                        'subType'      => $dataType['class'],
                        'required'     => false,
                        'default'      => isset($defaultProperties[$item->name]) ? $defaultProperties[$item->name] : null,
                        //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                        'description'  => $this->getDescription($item),
                        'readonly'     => $item->readOnly,
                        'sinceVersion' => $item->sinceVersion,
                        'untilVersion' => $item->untilVersion,
                    );

                    if (!is_null($dataType['class']) && false === $dataType['primitive']) {
                        $params[$name]['class'] = $dataType['class'];
                    }
                }

                // we can use type property also for custom handlers, then we don't have here real class name
                if (!class_exists($dataType['class'])) {
                    continue;
                }

                // if class already parsed, continue, to avoid infinite recursion
                if (in_array($dataType['class'], $visited)) {
                    continue;
                }

                // check for nested classes with JMS metadata
                if ($dataType['class'] && false === $dataType['primitive'] && null !== $this->factory->getMetadataForClass($dataType['class'])) {
                    $visited[] = $dataType['class'];
                    $children  = $this->doParse($dataType['class'], $visited, $groups);

                    if ($dataType['inline']) {
                        $params = array_merge($params, $children);
                    } else {
                        $params[$name]['children'] = $children;
                    }
                }
            }
        }

        return $params;
    }

    /**
     * Figure out a normalized data type (for documentation), and get a
     * nested class name, if available.
     *
     * @param  PropertyMetadata $item
     * @return array
     */
    protected function processDataType(PropertyMetadata $item)
    {
        // check for a type inside something that could be treated as an array
        if ($nestedType = $this->getNestedTypeInArray($item)) {
            if ($this->isPrimitive($nestedType)) {
                return array(
                    'normalized' => sprintf("array of %ss", $nestedType),
                    'actualType' => DataTypes::COLLECTION,
                    'class' => $this->typeMap[$nestedType],
                    'primitive' => true,
                    'inline' => false,
                );
            }

            $exp = explode("\\", $nestedType);

            return array(
                'normalized' => sprintf("array of objects (%s)", end($exp)),
                'actualType' => DataTypes::COLLECTION,
                'class' => $nestedType,
                'primitive' => false,
                'inline' => false,
            );
        }

        $type = $item->type['name'];

        // could be basic type
        if ($this->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'actualType' => $this->typeMap[$type],
                'class' => null,
                'primitive' => true,
                'inline' => false,
            );
        }

        // we can use type property also for custom handlers, then we don't have here real class name
        if (!class_exists($type)) {
            return array(
                'normalized' => sprintf("custom handler result for (%s)", $type),
                'class' => $type,
                'actualType' => DataTypes::MODEL,
                'primitive' => false,
                'inline' => false,
            );
        }

        // if we got this far, it's a general class name
        $exp = explode("\\", $type);

        return array(
            'normalized' => sprintf("object (%s)", end($exp)),
            'class' => $type,
            'actualType' => DataTypes::MODEL,
            'primitive' => false,
            'inline' => $item->inline,
        );
    }

    protected function isPrimitive($type)
    {
        return in_array($type, array('boolean', 'integer', 'string', 'float', 'double', 'array', 'DateTime'));
    }

    /**
     * {@inheritDoc}
     */
    public function postParse(array $input, array $parameters)
    {
        return $this->doPostParse($parameters);
    }

    /**
     * Recursive `doPostParse` to avoid circular post parsing.
     *
     * @param  array $parameters
     * @param  array $visited
     * @return array
     */
    protected function doPostParse (array $parameters, array $visited = array())
    {
        foreach ($parameters as $param => $data) {
            if (isset($data['class']) && isset($data['children']) && !in_array($data['class'], $visited)) {
                $visited[] = $data['class'];

                $input = array('class' => $data['class'], 'groups' => isset($data['groups']) ? $data['groups'] : array());
                $parameters[$param]['children'] = array_merge(
                    $parameters[$param]['children'], $this->doPostParse($parameters[$param]['children'], $visited)
                );
                $parameters[$param]['children'] = array_merge(
                    $parameters[$param]['children'], $this->doParse($input['class'], $visited, $input['groups'])
                );
            }
        }

        return $parameters;
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
