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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

final class SwaggerUiController
{
    private $generatorLocator;

    private $twig;

    /**
     * @param ContainerInterface $generatorLocator
     */
    public function __construct($generatorLocator, $twig)
    {
        if (!$twig instanceof \Twig_Environment && !$twig instanceof Environment) {
            throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" as twig is not supported.', get_class($twig)));
        }

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
        $this->twig = $twig;
    }

    public function __invoke(Request $request, $area = 'default')
    {
        if (!$this->generatorLocator->has($area)) {
            $advice = '';
            if (false !== strpos($area, '.json')) {
                $advice = ' Since the area provided contains `.json`, the issue is likely caused by route priorities. Try switching the Swagger UI / the json documentation routes order.';
            }

            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.%s', $area, $advice));
        }

        $spec = $this->generatorLocator->get($area)->generate()->toArray();
        if ('' !== $request->getBaseUrl()) {
            $spec['basePath'] = $request->getBaseUrl();
        }

        return new Response(
            $this->twig->render('@NelmioApiDoc/SwaggerUi/index.html.twig', ['swagger_data' => ['spec' => $spec]]),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );
    }
}
