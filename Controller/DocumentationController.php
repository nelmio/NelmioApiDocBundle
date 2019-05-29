<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Controller;

use Nelmio\ApiDocBundle\ApiDocGenerator;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class DocumentationController
{
    private $generatorLocator;

    /**
     * @param ContainerInterface $generatorLocator
     */
    public function __construct($generatorLocator)
    {
        if (!$generatorLocator instanceof ContainerInterface) {
            if (!$generatorLocator instanceof ApiDocGenerator) {
                throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" to "%s" is not supported.', get_class($generatorLocator), __METHOD__));
            }

            @trigger_error(sprintf('Providing an instance of "%s" to "%s()" is deprecated since version 3.1. Provide it an instance of "%s" instead.', ApiDocGenerator::class, __METHOD__, ContainerInterface::class), E_USER_DEPRECATED);
            $generatorLocator = new ServiceLocator(['default' => function () use ($generatorLocator): ApiDocGenerator {
                return $generatorLocator;
            }]);
        }

        $this->generatorLocator = $generatorLocator;
    }

    public function __invoke(Request $request, $area = 'default')
    {
        if (!$this->generatorLocator->has($area)) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.', $area));
        }

        $spec = $this->generatorLocator->get($area)->generate()->toArray();

        if ('' !== $request->getBaseUrl()) {
            $spec['basePath'] = $request->getBaseUrl();
        }

        if (empty($spec['host'])) {
            $spec['host'] = $request->getHost();
        }

        return new JsonResponse($spec);
    }
}
