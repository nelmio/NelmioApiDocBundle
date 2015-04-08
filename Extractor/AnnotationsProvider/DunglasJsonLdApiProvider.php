<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor\AnnotationsProvider;

use Doctrine\Common\Annotations\Reader;
use Dunglas\JsonLdApiBundle\JsonLd\ResourceCollectionInterface;
use Dunglas\JsonLdApiBundle\JsonLd\ResourceInterface;
use Dunglas\JsonLdApiBundle\Mapping\ClassMetadataFactory;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\AnnotationsProviderInterface;

/**
 * Creates ApiDoc annotations for DunglasJsonLdApiBundle.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class DunglasJsonLdApiProvider implements AnnotationsProviderInterface
{
    /**
     * @var ResourceCollectionInterface
     */
    private $resourceCollection;
    /**
     * @var ClassMetadataFactory
     */
    private $classMetadataFactory;

    public function __construct(ResourceCollectionInterface $resourceCollection, ClassMetadataFactory $classMetadataFactory)
    {
        $this->resourceCollection = $resourceCollection;
        $this->classMetadataFactory = $classMetadataFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getAnnotations()
    {
        $annotations = [];
        /**
         * @var ResourceInterface $resource
         */
        foreach ($this->resourceCollection as $resource) {
            $resource->getRouteCollection(); // Populate !route

            foreach ($resource->getCollectionOperations() as $operation) {
                $annotations[] = $this->getApiDoc($resource, $operation);
            }

            foreach ($resource->getItemOperations() as $operation) {
                $annotations[] = $this->getApiDoc($resource, $operation);
            }
        }

        return $annotations;
    }

    /**
     * Builds ApiDoc annotation from DunglasJsonLdApiBundle data.
     *
     * @param ResourceInterface $resource
     * @param array             $operation
     *
     * @return ApiDoc
     */
    private function getApiDoc(ResourceInterface $resource, array $operation)
    {
        $data = [
            'resource' => $operation['!route_path'],
            'description' => $operation['rdfs:label'],
            'resourceDescription' => $this->classMetadataFactory->getMetadataFor($resource->getEntityClass())->getDescription(),
        ];

        if (isset($operation['expects']) && $operation['expects'] !== 'owl:Nothing') {
            $data['input'] = $resource->getEntityClass();
        }

        if (isset($operation['returns']) && $operation['returns'] !== 'owl:Nothing') {
            $data['output'] = $resource->getEntityClass();
        }

        $data['filters'] = $resource->getFilters();

        $apiDoc = new ApiDoc($data);
        $apiDoc->setRoute($operation['!route']);

        return $apiDoc;
    }
}
