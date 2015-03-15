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

use Dunglas\JsonLdApiBundle\JsonLd\Resources;
use Dunglas\JsonLdApiBundle\Mapping\ClassMetadataFactory;
use Nelmio\ApiDocBundle\DataTypes;

/**
 * Use DunglasJsonLdApi to extract input and output information.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DunglasJsonLdApiParser implements ParserInterface
{
    /**
     * @var Resources
     */
    private $resources;
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    public function __construct(Resources $resources, ClassMetadataFactory $classMetadataFactory)
    {
        $this->resources = $resources;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        return null !== $this->resources->getResourceForEntity($item['class']);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $item)
    {
        /**
         * @var $resource \Dunglas\JsonLdApiBundle\JsonLd\Resource
         */
        $resource = $this->resources->getResourceForEntity($item['class']);
        $classMetadata = $this->classMetadataFactory->getMetadataFor(
            $resource->getEntityClass(),
            $resource->getNormalizationGroups(),
            $resource->getDenormalizationGroups(),
            $resource->getValidationGroups()
        );

        $data = array();
        foreach ($classMetadata->getAttributes() as $attribute) {
            $data[$attribute->getName()] = [
                'required' => $attribute->isRequired(),
                'description' => $attribute->getDescription(),
                'readonly' => $attribute->isReadable() && !$attribute->isWritable(),
                'class' => $resource->getEntityClass(),
            ];

            if (isset($attribute->getTypes()[0])) {
                $type = $attribute->getTypes()[0];
                if ($type->isCollection()) {
                    $dataType = DataTypes::COLLECTION;
                } elseif ('object' === $type->getType()) {
                    if ('DateTime' === $type->getClass()) {
                        $dataType = DataTypes::DATETIME;
                    } else {
                        $dataType = DataTypes::STRING;
                    }
                } else {
                    $dataType = $type->getType();
                }

                $data[$attribute->getName()]['dataType'] = $dataType;
            }
        }

        return $data;
    }
}
