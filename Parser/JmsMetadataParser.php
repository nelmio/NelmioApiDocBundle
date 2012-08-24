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

/**
 * Uses the JMS metadata factory to extract input/output model information
 */
class JmsMetadataParser implements ParserInterface
{

    /**
     * Constructor, requires JMS Metadata factory
     */
    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
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

                $params[$name] = array(
                    'dataType' => $this->getNormalizedType($item->type),
                    'required'      => false,   //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                    'description'   => $this->getDescription($input, $item->name),
                    'readonly' => $item->readOnly
                );

                //check for nested classes w/ JMS metadata
                if ($nestedInputClass = $this->getNestedClass($item->type)) {
                    $params[$name]['children'] = $this->parse($nestedInputClass);
                }
            }
        }

        return $params;
    }

    /**
     * There are various ways via JMS to declare arrays of objects, but that's an internal
     * implementation detail.
     *
     * @param  string $type
     * @return string
     */
    protected function getNormalizedType($type)
    {
        if (in_array($type, array('boolean', 'integer', 'string', 'double', 'array', 'DateTime'))) {
            return $type;
        }

        if (false !== strpos($type, "array") || false !== strpos($type, "ArrayCollection")) {
            if ($nested = $this->getNestedClassInArray($type)) {
                $exp = explode("\\", $nested);

                return sprintf("array of objects (%s)", end($exp));
            }
            
            return "array";
        }

        $exp = explode("\\", $type);

        return sprintf("object (%s)", end($exp));
    }

    /**
     * Check the various ways JMS describes custom classes in arrays, and
     * get the name of the class in the array, if available.
     *
     * @param  string       $type
     * @return string|null
     */
    protected function getNestedClassInArray($type)
    {
        //could be some type of array with <V>, or <K,V>
        $regEx = "/\<([A-Za-z0-9\\\]*)(\,?\s?(.*))?\>/";
        if (preg_match($regEx, $type, $matches)) {
            $matched = (!empty($matches[3])) ? $matches[3] : $matches[1];

            return in_array($matched, array('boolean', 'integer', 'string', 'double', 'array', 'DateTime')) ? false : $matched;
        }

        return null;
    }

    /**
     * Scan the JMS Serializer types for reference to a class.
     *
     * http://jmsyst.com/bundles/JMSSerializerBundle/master/reference/annotations#type
     *
     * @param  string       $type
     * @return string|null
     */
    protected function getNestedClass($type)
    {
        if (in_array($type, array('boolean', 'integer', 'string', 'double', 'array', 'DateTime'))) {
            return false;
        }

        //could be a nested object of some sort
        if ($nested = $this->getNestedClassInArray($type)) {
            return $nested;
        }

        //or could just be a class name (potentially)
        return (null === $this->factory->getMetadataForClass($type)) ? null : $type;
    }

    protected function getDescription($className, $propertyName)
    {
        $description = "No description.";

        //TODO: abstract docblock parsing utility and implement here
        return $description;
    }

}
