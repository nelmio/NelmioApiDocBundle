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
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex80;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\JMSComplex81;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup\JMSPicture;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\PrivateProtectedExposure;
use Nelmio\ApiDocBundle\Tests\Functional\Entity\SymfonyConstraintsWithValidationGroups;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\NameConverter;
use Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\VirtualTypeClassDoesNotExistsHandlerDefinedDescriber;
use Nelmio\ApiDocBundle\Tests\Helper;
use ReflectionException;
use ReflectionMethod;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class TestKernel extends Kernel
{
    use MicroKernelTrait;
    public const USE_JMS = 1;
    public const USE_BAZINGA = 2;
    public const USE_FOSREST = 3;
    public const USE_VALIDATION_GROUPS = 8;
    public const USE_FORM_CSRF = 16;

    private $flags;

    public function __construct(int $flags = 0)
    {
        parent::__construct('test'.$flags, true);

        $this->flags = $flags;
    }

    public function registerBundles(): iterable
    {
        $bundles = [
            new FrameworkBundle(),
            new TwigBundle(),
            new ApiPlatformBundle(),
            new NelmioApiDocBundle(),
            new TestBundle(),
        ];

        if (class_exists(SensioFrameworkExtraBundle::class)) {
            $bundles[] = new SensioFrameworkExtraBundle();
        }

        if (class_exists(FOSRestBundle::class)) {
            $bundles[] = new FOSRestBundle();
        }

        if ($this->flags & self::USE_JMS) {
            $bundles[] = new JMSSerializerBundle();

            if ($this->flags & self::USE_BAZINGA) {
                $bundles[] = new BazingaHateoasBundle();
            }
        }

        return $bundles;
    }

    protected function configureRoutes(RoutingConfigurator $routes)
    {
        if (self::isAnnotationsAvailable()) {
            $routes->withPath('/')->import(__DIR__.'/Resources/routes.yaml', 'yaml');
        } else {
            $routes->withPath('/')->import(__DIR__.'/Resources/routes-attributes.yaml', 'yaml');
        }

        if ($this->flags & self::USE_JMS) {
            $routes->withPath('/')->import(__DIR__.'/Controller/JMSController.php', self::isAnnotationsAvailable() ? 'annotation' : 'attribute');
        }

        if ($this->flags & self::USE_BAZINGA) {
            $routes->withPath('/')->import(__DIR__.'/Controller/BazingaTypedController.php', self::isAnnotationsAvailable() ? 'annotation' : 'attribute');

            try {
                new ReflectionMethod(Embedded::class, 'getType');
                $routes->withPath('/')->import(__DIR__.'/Controller/BazingaTypedController.php', self::isAnnotationsAvailable() ? 'annotation' : 'attribute');
            } catch (ReflectionException) {
            }
        }

        if ($this->flags & self::USE_FOSREST) {
            $routes->withPath('/')->import(__DIR__.'/Controller/FOSRestController.php', self::isAnnotationsAvailable() ? 'annotation' : 'attribute');
        }
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $framework = [
            'assets' => true,
            'secret' => 'MySecretKey',
            'test' => null,
            'validation' => null,
            'form' => null,
            'serializer' => [
                'mapping' => [
                    'paths' => [__DIR__.'/Resources/serializer/'],
                ],
            ],
            'property_access' => true,
        ];

        if (self::isAnnotationsAvailable()) {
            $loader->load(__DIR__.'/Configs/annotations.yaml');
        }

        if ($this->flags & self::USE_FORM_CSRF) {
            $framework['csrf_protection']['enabled'] = true;
            $framework['session']['storage_factory_id'] = 'session.storage.factory.mock_file';
            $framework['form'] = ['csrf_protection' => true];
        }

        $c->loadFromExtension('framework', $framework);

        $c->loadFromExtension('twig', [
            'strict_variables' => '%kernel.debug%',
            'exception_controller' => null,
        ]);

        if (self::isAnnotationsAvailable()) {
            $c->loadFromExtension('sensio_framework_extra', [
                'router' => [
                    'annotations' => false,
                ],
            ]);
        }

        $c->loadFromExtension('api_platform', [
            'mapping' => ['paths' => [
                !class_exists(ApiProperty::class)
                ? '%kernel.project_dir%/Tests/Functional/EntityExcluded/ApiPlatform3'
                : '%kernel.project_dir%/Tests/Functional/EntityExcluded/ApiPlatform2',
            ]],
        ]);

        if (self::isAnnotationsAvailable()) {
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
        }

        $models = [
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
                'alias' => 'SymfonyConstraintsTestGroup',
                'type' => SymfonyConstraintsWithValidationGroups::class,
                'groups' => ['test'],
            ],
            [
                'alias' => 'SymfonyConstraintsDefaultGroup',
                'type' => SymfonyConstraintsWithValidationGroups::class,
                'groups' => null,
            ],
        ];

        if (self::isAnnotationsAvailable()) {
            $models = array_merge($models, [
                [
                    'alias' => 'JMSComplex',
                    'type' => JMSComplex80::class,
                    'groups' => [
                        'list',
                        'details',
                        'User' => ['list'],
                    ],
                ],
                [
                    'alias' => 'JMSComplexDefault',
                    'type' => JMSComplex80::class,
                    'groups' => null,
                ],
            ]);
        } elseif (self::isAttributesAvailable()) {
            $models = array_merge($models, [
                [
                'alias' => 'JMSComplex',
                'type' => JMSComplex81::class,
                'groups' => [
                    'list',
                    'details',
                    'User' => ['list'],
                ],
                ],
                [
                    'alias' => 'JMSComplexDefault',
                    'type' => JMSComplex81::class,
                    'groups' => null,
                ],
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
                'names' => $models,
            ],
        ]);

        if ($this->flags & self::USE_JMS) {
            $c->loadFromExtension('jms_serializer', [
                'enum_support' => true,
            ]);
        }

        $def = new Definition(VirtualTypeClassDoesNotExistsHandlerDefinedDescriber::class);
        $def->addTag('nelmio_api_doc.model_describer');
        $c->setDefinition('nelmio.test.jms.virtual_type.describer', $def);

        $c->register('serializer.name_converter.custom', NameConverter::class)
            ->setDecoratedService('serializer.name_converter.metadata_aware')
            ->addArgument(new Reference('serializer.name_converter.custom.inner'));
    }

    public function getCacheDir(): string
    {
        return parent::getCacheDir().'/'.$this->flags;
    }

    public function getLogDir(): string
    {
        return parent::getLogDir().'/'.$this->flags;
    }

    public function serialize()
    {
        return serialize($this->useJMS);
    }

    public function unserialize($str): void
    {
        $this->__construct(unserialize($str));
    }

    public static function isAnnotationsAvailable(): bool
    {
        if (Kernel::MAJOR_VERSION >= 7) {
            return false;
        }

        return Helper::isDoctrineAnnotationsAvailable();
    }

    /**
     * @deprecated Remove this
     */
    public static function isAttributesAvailable(): bool
    {
        return true;
    }
}
