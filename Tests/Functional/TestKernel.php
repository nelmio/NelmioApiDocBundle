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

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Symfony\Bundle\ApiPlatformBundle;
use Bazinga\Bundle\HateoasBundle\BazingaHateoasBundle;
use FOS\RestBundle\FOSRestBundle;
use Hateoas\Configuration\Embedded;
use JMS\SerializerBundle\JMSSerializerBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\BazingaUser;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\PrivateProtectedExposure;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraintsWithValidationGroups;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\VirtualTypeClassDoesNotExistsHandlerDefinedDescriber;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Serializer\Annotation\SerializedName;

class TestKernel extends Kernel
{
    const USE_JMS = 1;
    const USE_BAZINGA = 2;
    const ERROR_ARRAY_ITEMS = 4;
    const USE_VALIDATION_GROUPS = 8;

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
    protected function configureRoutes($routes)
    {
        $this->import($routes, __DIR__.'/Resources/routes.yaml', '/', 'yaml');

        if (class_exists(SerializedName::class)) {
            $this->import($routes, __DIR__.'/Controller/SerializedNameController.php', '/', 'annotation');
        }

        if ($this->flags & self::USE_JMS) {
            $this->import($routes, __DIR__.'/Controller/JMSController.php', '/', 'annotation');
        }

        if ($this->flags & self::USE_BAZINGA) {
            $this->import($routes, __DIR__.'/Controller/BazingaController.php', '/', 'annotation');

            try {
                new \ReflectionMethod(Embedded::class, 'getType');
                $this->import($routes, __DIR__.'/Controller/BazingaTypedController.php', '/', 'annotation');
            } catch (\ReflectionException $e) {
            }
        }

        if ($this->flags & self::ERROR_ARRAY_ITEMS) {
            $this->import($routes, __DIR__.'/Controller/ArrayItemsErrorController.php', '/', 'annotation');
        }
    }

    /**
     * BC for sf < 5.1.
     */
    private function import($routes, $resource, $prefix, $type)
    {
        if ($routes instanceof RoutingConfigurator) {
            $routes->withPath($prefix)->import($resource, $type);
        } else {
            $routes->import($resource, $prefix, $type);
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
            'serializer' => [
                'enable_annotations' => true,
                'mapping' => [
                    'paths' => [__DIR__.'/Resources/serializer/'],
                ],
            ],
            'property_access' => true,
        ];
        // Support symfony/framework-bundle < 5.4
        if (method_exists(\Symfony\Bundle\FrameworkBundle\Command\CachePoolClearCommand::class, 'complete')) {
            $framework += [
                'exceptions' => [
                    'Symfony\Component\HttpKernel\Exception\BadRequestHttpException' => [
                        'log_level' => 'debug',
                    ],
                ],
            ];
        }
        $c->loadFromExtension('framework', $framework);

        $c->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
        ]);

        $c->loadFromExtension('api_platform', [
            'mapping' => ['paths' => [
                !class_exists(ApiProperty::class)
                ? '%kernel.project_dir%/Tests/Functional/EntityExcluded/ApiPlatform3'
                : '%kernel.project_dir%/Tests/Functional/EntityExcluded/ApiPlatform2',
            ]],
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

        // Filter routes
        $c->loadFromExtension('nelmio_api_doc', [
            'use_validation_groups' => boolval($this->flags & self::USE_VALIDATION_GROUPS),
            'documentation' => [
                'info' => [
                    'title' => 'My Default App',
                ],
                'paths' => [
                    // Ensures we can define routes in Yaml without defining OperationIds
                    // See https://github.com/zircote/swagger-php/issues/1153
                    '/api/test-from-yaml' => ['get' => [
                        'responses' => [
                            200 => ['description' => 'success'],
                        ],
                    ]],
                    '/api/test-from-yaml2' => ['get' => [
                        'responses' => [
                            200 => ['description' => 'success'],
                        ],
                    ]],
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
                        'type' => PrivateProtectedExposure::class,
                    ],
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
                    [
                        'alias' => 'JMSComplex',
                        'type' => JMSComplex::class,
                        'groups' => [
                            'list',
                            'details',
                            'User' => ['list'],
                        ],
                    ],
                    [
                        'alias' => 'JMSComplexDefault',
                        'type' => JMSComplex::class,
                        'groups' => null,
                    ],
                    [
                        'alias' => 'SymfonyConstraintsTestGroup',
                        'type' => SymfonyConstraintsWithValidationGroups::class,
                        'groups' => ['test'],
                    ],
                    [
                        'alias' => 'SymfonyConstraintsDefaultGroup',
                        'type' => SymfonyConstraintsWithValidationGroups::class,
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
