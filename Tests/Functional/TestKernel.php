<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\ApiPlatformBundle;
use FOS\RestBundle\FOSRestBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\ApiDocBundle\Tests\Functional\TestBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),
            new ApiPlatformBundle(),
            new NelmioApiDocBundle(),
            new FOSRestBundle(),
            new TestBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__.'/Controller/', '/', 'annotation');
        $routes->import('', '/api', 'api_platform');
        $routes->import('@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml', '/docs');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'secret' => 'MySecretKey',
            'test' => null,
            'validation' => null,
            'form' => null,
            'templating' => [
                'engines' => ['twig'],
            ],
            'serializer' => ['enable_annotations' => true],
        ]);

        $c->loadFromExtension('fos_rest', [
            'format_listener' => [
                'rules' => [
                    [
                        'path' => '^/',
                        'fallback_format' => 'json',
                    ]
                ],
            ],
        ]);

        // Filter routes
        $c->loadFromExtension('nelmio_api_doc', [
            'documentation' => [
                'info' => [
                    'title' => 'My Test App',
                ],
            ],
           'routes' => [
                'path_patterns' => ['^/api(?!/admin)'],
            ],
        ]);
    }
}
