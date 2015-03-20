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
use Dunglas\JsonLdApiBundle\JsonLd\Resource;
use Dunglas\JsonLdApiBundle\JsonLd\Resources;
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
    public function getAnnotations()
    {
        $annotations = [];
        foreach ($this->resources as $resource) {
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
     * @param Resource $resource
     * @param array $operation
     *
     * @return ApiDoc
     */
    private function getApiDoc(Resource $resource, array $operation)
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
