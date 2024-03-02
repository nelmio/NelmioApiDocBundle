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

.. code-block:: yaml

    # app/config/routing.yaml
    app.swagger_ui:
        path: /api/doc/{area}
        methods: GET
        defaults: { _controller: nelmio_api_doc.controller.swagger_ui, area: default }

    # With Redocly UI
    # app/config/routing.yaml
    #app.redocly:
    #    path: /api/doc/{area}
    #    methods: GET
    #    defaults: { _controller: nelmio_api_doc.controller.redocly, area: default }

    # To expose them as JSON
    #app.swagger.areas:
    #    path: /api/doc/{area}.json
    #    methods: GET
    #    defaults: { _controller: nelmio_api_doc.controller.swagger }


That's all! You can now access ``/api/doc/internal``, ``/api/doc/commercial`` and ``/api/doc/store``.

Use annotations to filter documented routes in each area
--------------------------------------------------------

You can use the `@Areas` annotation inside your controllers to define your routes' areas.

First, you need to define which areas will use the`@Areas` annotations to filter
the routes that should be documented:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            default:
                path_patterns: [ ^/api ]
            internal:
                with_annotation: true

Then add the annotation before your controller or action::

    use Nelmio\Annotations as Nelmio;

    /**
     * @Nelmio\Areas({"internal"}) => All actions in this controller are documented under the 'internal' area
     */
    class MyController
    {
        /**
         * @Nelmio\Areas({"internal"}) => This action is documented under the 'internal' area
         */
        public function index()
        {
           ...
        }
    }
