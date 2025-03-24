Configuration Reference
=======================

The bundle configuration is stored under the ``nelmio_api_doc`` key in your application configuration.

.. code-block:: terminal

    # displays the default config values
    $ php bin/console config:dump-reference nelmio_api_doc

    # displays the actual config values used by your application
    $ php bin/console debug:config nelmio_api_doc

.. code-block:: yaml

    # Example default configuration
    nelmio_api_doc:
        # Whether to use the symfony/type-info component for determining types.
        type_info: false
        # If true, `groups` passed to #[Model] attributes will be used to limit validation constraints
        use_validation_groups: false
        cache:
            # define cache pool to use
            pool: null
            # define cache item id
            item_id: null
        # The documentation to use as base
        documentation:
            # Example:
            # info:
            #   title: 'My App'
            #   description: 'My App Description'
         # List of enabled Media Types
        media_types:
            - json
        # UI configuration options
        html_config:
            assets_mode: cdn
            # https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
            swagger_ui_config: []
            # https://redocly.com/docs/redoc/config/
            redocly_config: []
            # https://docs.stoplight.io/docs/elements/b074dc47b2826-elements-configuration-options
            stoplight_config: []
        # Filter the routes that are documented
        areas:
            default:
                path_patterns:
                    # Examples:
                    # - ^/api
                    # - '^/api(?!/admin)'
                host_patterns:
                    # Example:
                    # - ^api\.
                name_patterns:
                    # Example:
                    # - ^api_v1
                # whether to filter by attributes
                with_attribute: false
                # if set disables default routes without attributes
                disable_default_routes: false
                # The base documentation used for the area
                documentation:
                    # Example:
                    # info:
                    #   title: 'My App'
                    #   description: 'My App Description'
                cache:
                    # define cache pool to use for the area
                    pool: null
                    # define cache item id for the area
                    item_id: null
        models:
            use_jms: false
            names:
                -
                    alias: ~ # Example: 'Foo'
                    type: ~ # Example: 'App\Foo'
                    groups: null
                    options: null
                    serializationContext: []
                    areas: []

Configuration
-------------

type_info
~~~~~~~~~

**type**: ``boolean``
**default**: ``false``

Whether to use `symfony/type-info`_ for determining types.

.. tip::

    If you are using Symfony 7.2 or higher, you should set this option to ``true``. As this greatly improves type detection.

.. versionadded:: 4.35

    Support for `symfony/type-info`_ was added in 4.35.

use_validation_groups
~~~~~~~~~~~~~~~~~~~~~

**type**: ``boolean``
**default**: ``false``

If true, ``groups`` passed to ``#[Model]`` attributes will be used to limit validation constraints.

cache
~~~~~

**type**: ``dictionary``
**allowed keys**: ``pool``, ``item_id``

Cache configuration for the generated documentation.

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            cache:
                # define cache pool to use
                pool: 'cache.app'
                # define cache item id
                item_id: 'nelmio_api_doc_cache'

documentation
~~~~~~~~~~~~~

**type**: ``dictionary``

The api documentation to use as base.

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            documentation:
                # Any valid OpenAPI/Swagger documentation
                info:
                    title: 'My App'
                    description: 'My App Description'

media_types
~~~~~~~~~~~

**type**: ``list``
**default**: ``['json']``
**allowed values**: ``json``, ``xml``

List of enabled Media Types.

html_config
~~~~~~~~~~~

**type**: ``dictionary``
**default**: ``[]``
**allowed keys**: ``assets_mode``, ``swagger_ui_config``, ``redocly_config``, ``stoplight_config``

UI configuration options.

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            html_config:
                assets_mode: 'cdn'
                # https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
                swagger_ui_config: []
                # https://redocly.com/docs/redoc/config/
                redocly_config: []
                # https://docs.stoplight.io/docs/elements/b074dc47b2826-elements-configuration-options
                stoplight_config: []

.. versionadded:: 4.37

    The `stoplight_config` option was added in 4.37.

areas
~~~~~

**type**: ``dictionary``

Filter the routes that are documented.

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            areas:
                default:
                    path_patterns:
                        # Examples:
                        # - ^/api
                        # - '^/api(?!/admin)'
                    host_patterns:
                        # Example:
                        # - ^api\.
                    name_patterns:
                        # Example:
                        # - ^api_v1
                    with_attribute: false
                    disable_default_routes: false
                    documentation:
                        # Example:
                        # info:
                        #   title: 'My App'
                        #   description: 'My App Description'
                    cache:
                        # define cache pool to use for the area
                        pool: null
                        # define cache item id for the area
                        item_id: null

path_patterns
.............

**type**: ``list``
**default**: ``[]``

List of regular expressions to match against the path of the route.

host_patterns
.............

**type**: ``list``
**default**: ``[]``

List of regular expressions to match against the host of the route.

name_patterns
.............

**type**: ``list``
**default**: ``[]``

List of regular expressions to match against the name of the route.

with_attribute
...............

**type**: ``boolean``
**default**: ``false``

Whether to only document routes with the ``#[Areas]`` annotation/attribute.

disable_default_routes
......................

**type**: ``boolean``
**default**: ``false``

If set, disables default routes without annotations/attributes.

documentation
.............

**type**: ``dictionary``
**default**: ``[]``

The base documentation used for the area.

cache
.....

**type**: ``dictionary``
**allowed keys**: ``pool``, ``item_id``

Cache configuration for the generated area documentation.

models
~~~~~~

**type**: ``dictionary``

Configuration for models.

use_jms
.......

**type**: ``boolean``
**default**: ``false``

Whether to use JMS Serializer for serialization.

names
.....

**type**: ``list``

List of models, this can be used to:
- Define models that are not automatically detected.
- Create a custom alias (schema name) for a model. (based groups/options/serializationContext/areas)

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            models:
                use_jms: false
                names:
                    -
                        # Alias the class 'App\Foo' to 'FooPrivate' for the 'private' group
                        alias: 'FooPrivate'
                        type: 'App\Foo'
                        groups:
                            - 'private'


.. _`symfony/type-info`: https://symfony.com/doc/current/components/type_info.html
