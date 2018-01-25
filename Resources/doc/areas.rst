Areas
=====

We've already seen that you can configure which routes are documented using ``nelmio_api_doc.areas``:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            path_patterns: [ ^/api ]

But in fact, this config option is way more powerful and allows you to split your documentation in several parts.

Configuration
-------------

You can define areas which will each generates a different documentation:

.. code-block:: yaml

    nelmio_api_doc:
        areas:
            default:
                path_patterns: [ ^/api ]
            admin:
                path_patterns: [ ^/api ]
                check_default: doc_area
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

That's all! You can now access ``/api/doc/internal``, ``/api/doc/admin`` and ``/api/doc/commercial``.

Advanced Configuration
----------------------

The admin area will now only show paths in ``/api`` that have the default doc_area set to contain admin.

.. code-block:: php

    namespace AppBundle\Controller;

    use AppBundle\Entity\User;
    use AppBundle\Entity\Reward;
    use Nelmio\ApiDocBundle\Annotation\Model;
    use Swagger\Annotations as SWG;
    use Symfony\Component\Routing\Annotation\Route;

    class AdminController
    {
        /*
         * will show in the admin area
         * @Route("/api/execute1", defaults={"doc_area" = {"admin"}})
         */
        public function executeAction()
        {
            // ...
        }

        /*
         * only in default area
         * @Route("/api/execute2")
         */
        public function executeAction()
        {
            // ...
        }
    }

..
