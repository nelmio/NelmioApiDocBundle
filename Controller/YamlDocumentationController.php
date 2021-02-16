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

final class YamlDocumentationController
{
    private $generatorLocator;

    public function __construct(ContainerInterface $generatorLocator)
    {
        $this->generatorLocator = $generatorLocator;
    }

    public function __invoke(Request $request, $area = 'default')
    {
        if (!$this->generatorLocator->has($area)) {
            throw new BadRequestHttpException(sprintf('Area "%s" is not supported as it isn\'t defined in config.', $area));
        }

        /** @var OpenApi $spec */
        $spec = $this->generatorLocator->get($area)->generate();

        if ('' !== $request->getBaseUrl()) {
            $spec->servers = [new Server(['url' => $request->getSchemeAndHttpHost().$request->getBaseUrl()])];
        }

        return new Response($spec->toYaml(), 200, [
            'Content-Type' => 'text/x-yaml',
        ]);
    }
}
