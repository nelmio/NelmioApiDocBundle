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
        if($meta = $this->factory->getMetadataForClass($input)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * {@inheritdoc}
     */
    public function parse($input)
    {
        die(__METHOD__);
        
        $meta = $this->factory->getMetadataForClass($input);

        if(is_null($meta)) {
            throw new \InvalidArgumentException(sprintf("No metadata found for class %s", $input));
        }
        
        $params = array();
        $refClass = new \ReflectionClass($input);
        
        //iterate over property metadata
        foreach ($meta->propertyMetadata as $item) {
            $name = isset($item->serializedName) ? $item->serializedName : $item->name;
            
            $type = $this->getType($item->type);
            
            if (true) {
                //TODO: check for nested type
            }
            
            $params[$name] = array(
                'dataType' => $item->type,
                'required'      => false,   //can't think of a good way to specify this one, JMS doesn't have a setting for this
                'description'   => $this->getDescription($refClass, $item->name),
                'readonly' => $item->readonly
            );
        }
        
        return $params;
    }
    
    protected function getDescription($ref, $nativePropertyName)
    {
        $description = "No description.";

        if (!$doc = $ref->getProperty($nativePropertyName)->getDocComment()) {
            return $description;
        }
        
        //TODO: regex comment to get description - or move doc comment parsing functionality from `ApiDocExtractor` to a new location
        //in order to reuse it here
                
        return $description;
    }
    
}
