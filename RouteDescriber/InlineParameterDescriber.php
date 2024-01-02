<?php

declare(strict_types=1);

namespace Nelmio\ApiDocBundle\RouteDescriber;

use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\RouteDescriber\InlineParameterDescriber\InlineParameterDescriberInterface;
use OpenApi\Annotations as OA;
use ReflectionMethod;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;
use Symfony\Component\Routing\Route;

final class InlineParameterDescriber implements RouteDescriberInterface, ModelRegistryAwareInterface
{
    use RouteDescriberTrait;
    use ModelRegistryAwareTrait;

    /**
     * @param InlineParameterDescriberInterface[] $inlineParameterDescribers
     */
    public function __construct(
        private ArgumentMetadataFactoryInterface $argumentMetadataFactory,
        private iterable $inlineParameterDescribers
    ) {
    }

    public function describe(OA\OpenApi $api, Route $route, ReflectionMethod $reflectionMethod): void
    {
        $controller = $route->getDefault('_controller');

        $argumentMetaDataList = $this->argumentMetadataFactory->createArgumentMetadata($controller, $reflectionMethod);

        if (!$argumentMetaDataList) {
            return;
        }

        foreach ($this->getOperations($api, $route) as $operation) {
            foreach ($argumentMetaDataList as $argumentMetadata) {
                foreach ($this->inlineParameterDescribers as $inlineParameterDescriber) {
                    if ($inlineParameterDescriber instanceof ModelRegistryAwareInterface) {
                        $inlineParameterDescriber->setModelRegistry($this->modelRegistry);
                    }

                    if (!$inlineParameterDescriber->supports($argumentMetadata)) {
                        continue;
                    }

                    $inlineParameterDescriber->describe($api, $operation, $argumentMetadata);
                }
            }
        }
    }
}
