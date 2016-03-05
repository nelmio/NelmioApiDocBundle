Configuration Reference
=======================

.. code-block:: yaml

    nelmio_api_doc:
        name:                 'API documentation'
        exclude_sections:     []
        default_sections_opened:  true
        motd:
            template:             'NelmioApiDocBundle::Components/motd.html.twig'
        request_listener:
            enabled:              true
            parameter:            _doc
        sandbox:
            enabled:              true
            endpoint:             null
            accept_type:          null
            body_format:
                formats:

                    # Defaults:
                    - form
                    - json
                default_format:       ~ # One of "form"; "json"
            request_format:
                formats:

                    # Defaults:
                    json:                application/json
                    xml:                 application/xml
                method:               ~ # One of "format_param"; "accept_header"
                default_format:       json
            authentication:
                delivery:             ~ # Required
                name:                 ~ # Required

                # Required if http delivery is selected.
                type:                 ~ # One of "basic"; "bearer"
                custom_endpoint:      false
            entity_to_choice:         true
        swagger:
            api_base_path:        /api
            swagger_version:      '1.2'
            api_version:          '0.1'
            info:
                title:                Symfony2
                description:          'My awesome Symfony2 app!'
                TermsOfServiceUrl:    null
                contact:              null
                license:              null
                licenseUrl:           null
        cache:
            enabled:              false
            file:                 '%kernel.cache_dir%/api-doc.cache'
