Configuration Reference
=============

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
                with_attribute:       false
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
