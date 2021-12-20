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
use Symfony\Component\Serializer\Annotation\SerializedName;

class TestKernel extends Kernel
{
    const USE_JMS = 1;
    const USE_BAZINGA = 2;
    const ERROR_ARRAY_ITEMS = 4;

    use MicroKernelTrait;

    private $flags;

    public function __construct(int $flags = 0)
    {
        parent::__construct('test'.$flags, true);

        $this->flags = $flags;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new TwigBundle(),
            new SensioFrameworkExtraBundle(),
            new ApiPlatformBundle(),
            new NelmioApiDocBundle(),
            new TestBundle(),
            new FOSRestBundle(),
        ];

        if ($this->flags & self::USE_JMS) {
            $bundles[] = new JMSSerializerBundle();

            if ($this->flags & self::USE_BAZINGA) {
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
        $controllerPath = __DIR__.'/Controller'.(\PHP_VERSION_ID >= 80100 ? 'Php81' : '');

        $routes->import($controllerPath.'/TestController.php', '/', 'annotation');
        $routes->import($controllerPath.'/ApiController.php', '/', 'annotation');
        $routes->import($controllerPath.'/ClassApiController.php', '/', 'annotation');
        $routes->import($controllerPath.'/UndocumentedController.php', '/', 'annotation');
        $routes->import($controllerPath.'/InvokableController.php', '/', 'annotation');
        $routes->import('', '/api', 'api_platform');
        $routes->add('/docs/{area}', 'nelmio_api_doc.controller.swagger_ui')->setDefault('area', 'default');
        $routes->add('/docs.json', 'nelmio_api_doc.controller.swagger_json');
        $routes->add('/docs.yaml', 'nelmio_api_doc.controller.swagger_yaml');
        $routes->import($controllerPath.'/FOSRestController.php', '/', 'annotation');

        if (class_exists(SerializedName::class)) {
            $routes->import($controllerPath.'/SerializedNameController.php', '/', 'annotation');
        }

        if ($this->flags & self::USE_JMS) {
            $routes->import($controllerPath.'/JMSController.php', '/', 'annotation');
        }

        if ($this->flags & self::USE_BAZINGA) {
            $routes->import($controllerPath.'/BazingaController.php', '/', 'annotation');

            try {
                new \ReflectionMethod(Embedded::class, 'getType');
                $routes->import($controllerPath.'/BazingaTypedController.php', '/', 'annotation');
            } catch (\ReflectionException $e) {
            }
        }

        if ($this->flags & self::ERROR_ARRAY_ITEMS) {
            $routes->import($controllerPath.'/ArrayItemsErrorController.php', '/', 'annotation');
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
            'property_access' => true,
        ];

        $c->loadFromExtension('framework', $framework);

        $c->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
        ]);

        $c->loadFromExtension('sensio_framework_extra', [
            'router' => [
                'annotations' => false,
            ],
        ]);

        $c->loadFromExtension('api_platform', [
            'mapping' => ['paths' => ['%kernel.project_dir%/Tests/Functional/Entity'.(\PHP_VERSION_ID >= 80100 ? 'Php81' : '')]],
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

        // If FOSRestBundle 2.8
        if (class_exists(\FOS\RestBundle\EventListener\ResponseStatusCodeListener::class)) {
            $c->loadFromExtension('fos_rest', [
                'exception' => [
                    'enabled' => false,
                    'exception_listener' => false,
                    'serialize_exceptions' => false,
                ],
                'body_listener' => false,
                'routing_loader' => false,
            ]);
        }

        if (\PHP_VERSION_ID >= 80100) {
            $privateProtectedExposureClass = \Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\PrivateProtectedExposure::class;
            $jMSPictureClass = \Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\NestedGroup\JMSPicture::class;
            $bazingaUserClass = \Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\BazingaUser::class;
            $jMSComplexClass = \Nelmio\ApiDocBundle\Tests\Functional\EntityPhp81\JMSComplex::class;
        } else {
            $privateProtectedExposureClass = \Nelmio\ApiDocBundle\Tests\Functional\Entity\PrivateProtectedExposure::class;
            $jMSPictureClass = \Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture::class;
            $bazingaUserClass = \Nelmio\ApiDocBundle\Tests\Functional\Entity\BazingaUser::class;
            $jMSComplexClass = \Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex::class;
        }

        // Filter routes
        $c->loadFromExtension('nelmio_api_doc', [
            'documentation' => [
                'servers' => [ // from https://github.com/nelmio/NelmioApiDocBundle/issues/1691
                    [
                        'url' => 'https://api.example.com/secured/{version}',
                        'variables' => ['version' => ['default' => 'v1']],
                    ],
                ],
                'info' => [
                    'title' => 'My Default App',
                ],
                'components' => [
                    'schemas' => [
                        'Test' => [
                            'type' => 'string',
                        ],

                        // Ensures https://github.com/nelmio/NelmioApiDocBundle/issues/1650 is working
                        'Pet' => [
                            'type' => 'object',
                        ],
                        'Cat' => [
                            'allOf' => [
                                ['$ref' => '#/components/schemas/Pet'],
                                ['type' => 'object'],
                            ],
                        ],
                        'AddProp' => [
                            'type' => 'object',
                            'additionalProperties' => false,
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
                        'alias' => 'PrivateProtectedExposure',
                        'type' => $privateProtectedExposureClass,
                    ],
                    [
                        'alias' => 'JMSPicture_mini',
                        'type' => $jMSPictureClass,
                        'groups' => ['mini'],
                    ],
                    [
                        'alias' => 'BazingaUser_grouped',
                        'type' => $bazingaUserClass,
                        'groups' => ['foo'],
                    ],
                    [
                        'alias' => 'JMSComplex',
                        'type' => $jMSComplexClass,
                        'groups' => [
                            'list',
                            'details',
                            'User' => ['list'],
                        ],
                    ],
                    [
                        'alias' => 'JMSComplexDefault',
                        'type' => $jMSComplexClass,
                        'groups' => null,
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
    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/'.$this->flags;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return parent::getLogDir().'/'.$this->flags;
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
