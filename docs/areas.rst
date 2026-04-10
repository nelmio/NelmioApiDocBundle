Areas
=====

We've already seen that you can configure which routes are documented using ``nelmio_api_doc.areas``:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            path_patterns: [ ^/api ]
            host_patterns: [ ^api\. ]
            name_patterns: [ ^api_v1 ]

But in fact, this config option is way more powerful and allows you to split your documentation in several parts.

Configuration
-------------

You can define areas which will each generates a different documentation:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            default:
                path_patterns: [ ^/api ]
                host_patterns: [ ^api\. ]
            internal:
                path_patterns: [ ^/internal ]
            commercial:
                path_patterns: [ ^/commercial ]
            store:
                # Includes routes with names containing 'store'
                name_patterns: [ store ]


Your main documentation is under the ``default`` area. It's the one shown when accessing ``/api/doc``.

Then update your routing to be able to access your different documentations:

.. configuration-block::

    .. code-block:: yaml

        # config/routes/nelmio_api_doc.yaml
        app.swagger_ui:
            path: /api/doc/{area}
            methods: GET
            defaults: { _controller: nelmio_api_doc.controller.swagger_ui, area: default }

        # With Redocly UI (use instead of Swagger UI)
        # app.redocly:
        #     path: /api/doc/{area}
        #     methods: GET
        #     defaults: { _controller: nelmio_api_doc.controller.redocly, area: default }

        # With Stoplight (use instead of Swagger UI)
        # app.stoplight:
        #     path: /api/doc/{area}
        #     methods: GET
        #     defaults: { _controller: nelmio_api_doc.controller.stoplight, area: default }

        # With Scalar (use instead of Swagger UI)
        # app.scalar:
        #     path: /api/doc/{area}
        #     methods: GET
        #     defaults: { _controller: nelmio_api_doc.controller.scalar, area: default }

        # To expose them as JSON
        # app.swagger.areas:
        #     path: /api/doc/{area}.json
        #     methods: GET
        #     defaults: { _controller: nelmio_api_doc.controller.swagger, area: default }

    .. code-block:: php

        // config/routes/nelmio_api_doc.php
        use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

        return static function (RoutingConfigurator $routes): void {
            $routes->add('app.swagger_ui', '/api/doc/{area}')
                ->controller('nelmio_api_doc.controller.swagger_ui')
                ->methods(['GET'])
                ->defaults(['area' => 'default']);

            // With Redocly UI
            // $routes->add('app.redocly', '/api/doc/{area}')
            //     ->controller('nelmio_api_doc.controller.redocly')
            //     ->methods(['GET'])
            //     ->defaults(['area' => 'default']);

            // With Stoplight
            // $routes->add('app.stoplight', '/api/doc/{area}')
            //     ->controller('nelmio_api_doc.controller.stoplight')
            //     ->methods(['GET'])
            //     ->defaults(['area' => 'default']);

            // With Scalar
            // $routes->add('app.scalar', '/api/doc/{area}')
            //     ->controller('nelmio_api_doc.controller.scalar')
            //     ->methods(['GET'])
            //     ->defaults(['area' => 'default']);

            // To expose them as JSON
            // $routes->add('app.swagger.areas', '/api/doc/{area}.json')
            //     ->controller('nelmio_api_doc.controller.swagger')
            //     ->methods(['GET'])
            //     ->defaults(['area' => 'default']);
        };

That's all! You can now access ``/api/doc/internal``, ``/api/doc/commercial`` and ``/api/doc/store``.

Use attributes to filter documented routes in each area
--------------------------------------------------------

You can use the ``#[Areas]`` attribute inside your controllers to define your routes' areas.

First, you need to define which areas will use the ``#[Areas]`` attributes to filter
the routes that should be documented:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            default:
                path_patterns: [ ^/api ]
            internal:
                with_attribute: true

Then add the attribute before your controller or action::

.. configuration-block::

    .. code-block:: php-attributes

        use Nelmio\ApiDocBundle\Attribute as Nelmio;

        /**
         * All actions in this controller are documented under the 'internal' area
         */
        #[Nelmio\Areas(["internal"])]
        class MyController
        {
            /**
             * This action is documented under the 'internal' area
             */
            #[Nelmio\Areas(["internal"])]
            public function index()
            {
               ...
            }
        }
