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

use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Metadata\VirtualPropertyMetadata;

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
     * @var \Nelmio\ApiDocBundle\Util\DocCommentExtractor
     */
    private $commentExtractor;

    private $parsedClasses = array();

    /**
     * Constructor, requires JMS Metadata factory
     */
    public function __construct(MetadataFactoryInterface $factory, DocCommentExtractor $commentExtractor)
    {
        $this->factory = $factory;
        $this->commentExtractor = $commentExtractor;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($input)
    {
        try {
            if ($meta = $this->factory->getMetadataForClass($input)) {
                return true;
            }
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($input)
    {
        $meta = $this->factory->getMetadataForClass($input);

        if (null === $meta) {
            throw new \InvalidArgumentException(sprintf("No metadata found for class %s", $input));
        }

        $params = array();

        //iterate over property metadata
        foreach ($meta->propertyMetadata as $item) {
            if (!is_null($item->type)) {
                $name = isset($item->serializedName) ? $item->serializedName : $item->name;

                $dataType = $this->processDataType($item);

                $params[$name] = array(
                    'dataType' => $dataType['normalized'],
                    'required'      => false,   //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                    'description'   => $this->getDescription($input, $item),
                    'readonly' => $item->readOnly
                );

                // if class already parsed, continue, to avoid infinite recursion
                if (in_array($dataType['class'], $this->parsedClasses)) {
                    continue;
                }

                //check for nested classes with JMS metadata
                if ($dataType['class'] && null !== $this->factory->getMetadataForClass($dataType['class'])) {
                    $this->parsedClasses[] = $dataType['class'];
                    $params[$name]['children'] = $this->parse($dataType['class']);
                }
            }
        }
        $this->parsedClasses = array();

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
        $type = is_string($item->type) ? $item->type : $item->type['name'];

        //check for a type inside something that could be treated as an array
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

        //could be basic type
        if ($this->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'class' => null
            );
        }

        //if we got this far, it's a general class name
        $exp = explode("\\", $type);

        return array(
            'normalized' => sprintf("object (%s)", end($exp)),
            'class' => $type
        );
    }

    protected function isPrimitive($type)
    {
        return in_array($type, array('boolean', 'integer', 'string', 'double', 'array', 'DateTime'));
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
        if (is_array($item->type)
            && in_array($item->type['name'], array('array')) // We have to support ArrayCollection as well
            && isset($item->type['params'])
            && 1 === count($item->type['params'])
            && isset($item->type['params'][0]['name'])) {
            return $item->type['params'][0]['name'];
        }

        return null;
    }

    protected function getDescription($className, PropertyMetadata $item)
    {
        $ref = new \ReflectionClass($className);
        if ($item instanceof VirtualPropertyMetadata) {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getMethod($item->getter));
        } else {
            $extracted = $this->commentExtractor->getDocCommentText($ref->getProperty($item->name));
        }

        return !empty($extracted) ? $extracted : "No description.";
    }

}
