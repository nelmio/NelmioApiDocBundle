Areas
=====

We've already seen that you can configure which routes are documented using ``nelmio_api_doc.areas``:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            path_patterns: [ ^/api ]
            host_patterns: [ ^api\. ]

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

Your main documentation is under the ``default`` area. It's the one shown when accessing ``/api/doc``.

Then update your routing to be able to access your different documentations:

.. code-block:: yaml

    # app/config/routing.yml
    app.swagger_ui:
        path: /api/doc/{area}
        methods: GET
        defaults: { _controller: nelmio_api_doc.controller.swagger_ui, area: default }

    # To expose them as JSON
    #app.swagger.areas:
    #    path: /api/doc/{area}.json
    #    methods: GET
    #    defaults: { _controller: nelmio_api_doc.controller.swagger }

That's all! You can now access ``/api/doc/internal`` and ``/api/doc/commercial``.
