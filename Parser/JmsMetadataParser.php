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

                $dataType = $this->processDataType($item->type);

                $params[$name] = array(
                    'dataType' => $dataType['normalized'],
                    'required'      => false,   //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                    'description'   => $this->getDescription($input, $item->name),
                    'readonly' => $item->readOnly
                );

                //check for nested classes with JMS metadata
                if ($dataType['class'] && null !== $this->factory->getMetadataForClass($dataType['class'])) {
                    $params[$name]['children'] = $this->parse($dataType['class']);
                }
            }
        }

        return $params;
    }

    /**
     * Figure out a normalized data type (for documentation), and get a
     * nested class name, if available.
     *
     * @param  string $type
     * @return array
     */
    protected function processDataType($type)
    {
        //could be basic type
        if ($this->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'class' => null
            );
        }

        //check for a type inside something that could be treated as an array
        if ($nestedType = $this->getNestedTypeInArray($type)) {
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
     * @param  string      $type
     * @return string|null
     */
    protected function getNestedTypeInArray($type)
    {
        //could be some type of array with <V>, or <K,V>
        $regEx = "/\<([A-Za-z0-9\\\]*)(\,?\s?(.*))?\>/";
        if (preg_match($regEx, $type, $matches)) {
            return (!empty($matches[3])) ? $matches[3] : $matches[1];
        }

        return null;
    }

    protected function getDescription($className, $propertyName)
    {
        $description = "No description.";

        //TODO: abstract docblock parsing utility and implement here
        return $description;
    }

}
