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
use JMS\SerializerBundle\Metadata\PropertyMetadata;
use JMS\SerializerBundle\Metadata\VirtualPropertyMetadata;
use Nelmio\ApiDocBundle\Util\DataTypeParser;

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
    
    /**
     * @var \Nelmio\ApiDocBundle\Util\DataTypeParser
     */
    private $dataTypeParser;

    private $parsedClasses = array();

    /**
     * Constructor, requires JMS Metadata factory
     */
    public function __construct(MetadataFactoryInterface $factory, DocCommentExtractor $commentExtractor, DataTypeParser $dataTypeParser)
    {
        $this->factory = $factory;
        $this->commentExtractor = $commentExtractor;
        $this->dataTypeParser = $dataTypeParser;
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
     * @param  string $type
     * @return array
     */
    protected function processDataType($type)
    {
        //could be basic type
        if ($this->dataTypeParser->isPrimitive($type)) {
            return array(
                'normalized' => $type,
                'class' => null
            );
        }

        //check for a type inside something that could be treated as an array
        if ($nestedType = $this->dataTypeParser->getNestedTypeInArray($type)) {
            if ($this->dataTypeParser->isPrimitive($nestedType['value'])) {
                return array(
                    'normalized' => sprintf("array<%s>", $nestedType['value']),
                    'class' => null
                );
            }

            $exp = explode("\\", $nestedType['value']);

            return array(
                'normalized' => sprintf("array<%s>", end($exp)),
                'class' => $nestedType['value']
            );
        }

        //if we got this far, it's a general class name
        $exp = explode("\\", $type);

        return array(
            'normalized' => sprintf("%s", end($exp)),
            'class' => $type
        );
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
