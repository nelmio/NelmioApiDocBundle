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
use Hateoas\Configuration\Embedded;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\BazingaUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\VirtualTypeClassDoesNotExistsHandlerDefinedDescriber;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
            new TestBundle(),
        ];

        if (class_exists(FOSRestBundle::class)) {
            $bundles[] = new FOSRestBundle();
        }

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

        if (class_exists(FOSRestBundle::class)) {
            $routes->import(__DIR__.'/Controller/FOSRestController.php', '/', 'annotation');
        }

        if ($this->useJMS) {
            $routes->import(__DIR__.'/Controller/JMSController.php', '/', 'annotation');
        }

        if ($this->useBazinga) {
            $routes->import(__DIR__.'/Controller/BazingaController.php', '/', 'annotation');

            try {
                new \ReflectionMethod(Embedded::class, 'getType');
                $routes->import(__DIR__.'/Controller/BazingaTypedController.php', '/', 'annotation');
            } catch (\ReflectionException $e) {
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $framework = [
            'assets' => true,
            'secret' => 'MySecretKey',
            'test' => null,
            'validation' => null,
            'form' => null,
            'serializer' => ['enable_annotations' => true],
        ];

        // templating is deprecated
        if (Kernel::VERSION_ID <= 40300) {
            $framework['templating'] = ['engines' => ['twig']];
        }

        $c->loadFromExtension('framework', $framework);

        $c->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
        ]);

        $c->loadFromExtension('sensio_framework_extra', [
            'router' => [
                'annotations' => false,
            ],
        ]);

        $c->loadFromExtension('api_platform', [
            'mapping' => ['paths' => ['%kernel.project_dir%/Tests/Functional/Entity']],
        ]);

        if (class_exists(FOSRestBundle::class)) {
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
        }

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
            'models' => [
                'names' => [
                    [
                        'alias' => 'JMSPicture_mini',
                        'type' => JMSPicture::class,
                        'groups' => ['mini'],
                    ],
                    [
                        'alias' => 'BazingaUser_grouped',
                        'type' => BazingaUser::class,
                        'groups' => ['foo'],
                    ],
                ],
            ],
        ]);

        $def = new Definition(VirtualTypeClassDoesNotExistsHandlerDefinedDescriber::class);
        $def->addTag('nelmio_api_doc.model_describer');
        $c->setDefinition('nelmio.test.jms.virtual_type.describer', $def);
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
