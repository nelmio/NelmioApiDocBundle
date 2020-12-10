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

use OpenApi\Annotations\OpenApi;
use OpenApi\Annotations\Server;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Twig\Environment;

final class SwaggerUiController
{
    private $generatorLocator;

    private $twig;

    public function __construct(ContainerInterface $generatorLocator, $twig)
    {
        if (!$twig instanceof \Twig_Environment && !$twig instanceof Environment) {
            throw new \InvalidArgumentException(sprintf('Providing an instance of "%s" as twig is not supported.', get_class($twig)));
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

        /** @var OpenApi $spec */
        $spec = $this->generatorLocator->get($area)->generate();

        if ('' !== $request->getBaseUrl()) {
            $spec->servers = [new Server(['url' => $request->getSchemeAndHttpHost().$request->getBaseUrl()])];
        }

        return new Response(
            $this->twig->render(
                '@NelmioApiDoc/SwaggerUi/index.html.twig',
                ['swagger_data' => ['spec' => json_decode($spec->toJson(), true)]]
            ),
            Response::HTTP_OK,
            ['Content-Type' => 'text/html']
        );

        return $response->setCharset('UTF-8');
    }
}
