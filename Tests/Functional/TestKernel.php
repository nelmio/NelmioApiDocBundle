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
use Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle;
use FOS\RestBundle\FOSRestBundle;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
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

    private $useJMS;
    private $useBazinga;

    public function __construct(bool $useJMS = false, bool $useBazinga = false)
    {
        parent::__construct('test'.(int) $useJMS.(int) $useBazinga, true);

        $this->useJMS = $useJMS;
        $this->useBazinga = $useBazinga;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),
            new ApiPlatformBundle(),
            new NelmioApiDocBundle(),
            new FOSRestBundle(),
            new TestBundle(),
        ];

        if ($this->useJMS) {
            $bundles[] = new JMSSerializerBundle();

            if ($this->useBazinga) {
                $bundles[] = new BazingaHateoasBundle();
            }
        }

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__.'/Controller/TestController.php', '/', 'annotation');
        $routes->import(__DIR__.'/Controller/ApiController.php', '/', 'annotation');
        $routes->import(__DIR__.'/Controller/ClassApiController.php', '/', 'annotation');
        $routes->import(__DIR__.'/Controller/UndocumentedController.php', '/', 'annotation');
        $routes->import('', '/api', 'api_platform');
        $routes->add('/docs/{area}', 'nelmio_api_doc.controller.swagger_ui')->setDefault('area', 'default');
        $routes->add('/docs.json', 'nelmio_api_doc.controller.swagger');

        if ($this->useJMS) {
            $routes->import(__DIR__.'/Controller/JMSController.php', '/', 'annotation');
        }

        if ($this->useBazinga) {
            $routes->import(__DIR__.'/Controller/BazingaController.php', '/', 'annotation');
        }
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

        $c->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
        ]);

        $c->loadFromExtension('fos_rest', [
            'format_listener' => [
                'rules' => [
                    [
                        'path' => '^/',
                        'fallback_format' => 'json',
                    ],
                ],
            ],
        ]);

        // Filter routes
        $c->loadFromExtension('nelmio_api_doc', [
            'documentation' => [
                'info' => [
                    'title' => 'My Default App',
                ],
                'definitions' => [
                    'Test' => [
                        'type' => 'string',
                    ],
                ],
                'parameters' => [
                    'test' => [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                    ],
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Awesome description',
                    ],
                ],
            ],
           'areas' => [
               'default' => [
                   'path_patterns' => ['^/api(?!/admin)'],
                   'host_patterns' => ['^api\.'],
               ],
               'test' => [
                   'path_patterns' => ['^/test'],
                   'host_patterns' => ['^api-test\.'],
                   'documentation' => [
                       'info' => [
                           'title' => 'My Test App',
                       ],
                   ],
               ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return parent::getCacheDir().'/'.(int) $this->useJMS;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return parent::getLogDir().'/'.(int) $this->useJMS;
    }

    public function serialize()
    {
        return serialize($this->useJMS);
    }

    public function unserialize($str)
    {
        $this->__construct(unserialize($str));
    }
}
