<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Doctrine\Common\Persistence\Mapping\MappingException;
use EXSyst\Component\Swagger\Schema;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\Metadata\PropertyMetadata;
use JMS\Serializer\Naming\PropertyNamingStrategyInterface;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use phpDocumentor\Reflection\DocBlockFactory;
use Symfony\Component\PropertyInfo\Type;

/**
 * Uses the JMS metadata factory to extract input/output model information.
 */
class DoctrineModelDescriber implements ModelDescriberInterface,
    ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;
    private $namingStrategy;
    private $use_jms;

    public function __construct(
        $factory,
        PropertyNamingStrategyInterface $namingStrategy,
        $use_jms
    ) {
        $this->factory = $factory;
        $this->namingStrategy = $namingStrategy;
        $this->use_jms = $use_jms;
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Model $model, Schema $schema)
    {
        $className = $model->getType()->getClassName();
        $metadata = $this->factory->getClassMetadata($className);

        if (null === $metadata) {
            throw new \InvalidArgumentException(sprintf('No metadata found for class %s.',
                $className));
        }

        $schema->setType('object');

        $reqarr = [];
        $properties = $schema->getProperties();

        foreach ($metadata->fieldMappings as $name => $item) {
            if ($this->use_jms && !$properties->has($name)) {
                continue;
            }

            $property = $properties->get($name);
            if ($property->getType() == null) {
                $property->setType($item['type']);
            }
            //get Required
            if (array_key_exists('nullable', $item)
                && false == $item['nullable']
            ) {
                $reqarr[] = $name;
            }

            if (array_key_exists('options', $item)) {
                //default
                if (array_key_exists('default', $item['options'])) {
                    $property->setDefault($item['options']["default"]);
                }

                if (array_key_exists('comment', $item['options'])) {
                    //get description
                    if (array_key_exists('description',
                        $item['options']["comment"])
                    ) {
                        $property->setDescription($item['options']["comment"]['description']);
                    }
                    //get format
                    if (array_key_exists('format',
                        $item['options']["comment"])
                    ) {
                        $property->setFormat($item['options']["comment"]['format']);
                    }
                }
            }
        }
        $schema->setRequired($reqarr);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model): bool
    {
        $className = $model->getType()->getClassName();
        try {
            if ($this->factory->getClassMetadata($className)) {
                return true;
            }
        } catch (MappingException $e) {
        }

        return false;
    }
}
