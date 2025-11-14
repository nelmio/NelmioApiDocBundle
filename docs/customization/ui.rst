UI Customization
=============

The look and feel of the Swagger UI can be customized.

Overwrite Twig Template
-----------------------

If you want to customize parts of the template, you can create your own Twig template.
This allows to change Swagger UI configuration, page title, page header, add additional or replace existing styles or scripts.

Take a look at the Twig documentation `how to extend templates <https://twig.symfony.com/doc/2.x/tags/extends.html>`_.

The following example will add additional scripts and a custom style to the template.
Just create a file ``templates/bundles/NelmioApiDocBundle/SwaggerUi/index.html.twig``.

.. code-block:: twig

    {# templates/bundles/NelmioApiDocBundle/SwaggerUi/index.html.twig #}

    {#
        To avoid a "reached nested level" error an exclamation mark `!` has to be added
        See https://symfony.com/blog/new-in-symfony-3-4-improved-the-overriding-of-templates
    #}
    {% extends '@!NelmioApiDoc/SwaggerUi/index.html.twig' %}

    {#
        Change Swagger UI configuration
        All parameters are explained on Swagger UI website:
        https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
    #}
    {% block swagger_initialization %}
        <script type="text/javascript">
            window.onload = () => {
                loadSwaggerUI({
                    defaultModelsExpandDepth: -1,
                    deepLinking: true,
                });
            };
        </script>
    {% endblock %}

    {#
        Change Redocly configuration
        All parameters are explained on Redocly website:
        https://redocly.com/docs/redoc/config/
    #}
    {% block swagger_initialization %}
        <script type="text/javascript">
            window.onload = () => {
                loadRedocly({
                    expandResponses: '200,201',
                    hideDownloadButton: true,
                });
            };
        </script>
    {% endblock %}

    {# Import your own stylesheet #}
    {% block stylesheets %}
        {{ parent() }}
        <link rel="stylesheet" href="{{ asset('css/custom-swagger-styles.css') }}">
    {% endblock stylesheets %}

    {# Import your own script #}
    {% block javascripts %}
        {{ parent() }}
        <script type="text/javascript" src="{{ asset('js/custom-request-signer.js') }}"></script>
    {% endblock javascripts %}

You can have a look at the `original template <https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/templates/SwaggerUi/index.html.twig>`_, in ``/templates/SwaggerUi/index.html.twig``, to see which blocks can be overridden.

Assets Loading Options
-----------------------

The `html_config` settings allow you to configure how assets are loaded for the UI. The `assets_mode` option supports three values: `cdn`, `bundle`, and `offline`.


   .. code-block:: yaml

       nelmio_api_doc:
           html_config:
               assets_mode: 'cdn' # Other values: 'bundle', 'offline'

`assets_mode`
~~~~~~~~~~~~~

The three values possible values can be found in `AssetsMode.php <https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/Render/Html/AssetsMode.php>`_
- **cdn**: Loads assets from `jsDelivr <https://www.jsdelivr.com/>`_.
- **bundle**: Fetches assets from the bundle in the vendor directory, including updates.
- **offline**: Loads assets from the local `assets` directory, requiring the developer to update them manually.
