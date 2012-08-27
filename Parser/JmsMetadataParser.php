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

                //TODO: check for nested type

                $params[$name] = array(
                    'dataType' => $item->type,
                    'required'      => false,   //TODO: can't think of a good way to specify this one, JMS doesn't have a setting for this
                    'description'   => $this->getDescription($input, $item->name),
                    'readonly' => $item->readOnly
                );
            }
        }

        return $params;
    }

    protected function getDescription($className, $propertyName)
    {
        $description = "No description.";

        //TODO: regex comment to get description - or move doc comment parsing functionality from `ApiDocExtractor` to a new location
        //in order to reuse it here
        
        return $description;
    }

}
