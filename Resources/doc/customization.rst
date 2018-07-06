Customization
=============

The look and feel of the Swagger UI can be customized.


Overwrite Twig Template
-----------------------

If you want to customize parts of the template, you can create your own Twig template.
This allows to change the title, the header, add additional or replace existing styles or scripts.

Add the template name to the configuration:

.. code-block:: yaml

    nelmio_api_doc:
        config:
            template: /path/to/template.html.twig

Take a look at the Twig documentation `how to extend templates <https://twig.symfony.com/doc/2.x/tags/extends.html>`_.

The following example will add additional scripts and a custom style to the template:

.. code-block:: twig

    {% extends '@NelmioApiDoc/SwaggerUi/index.html.twig' %}

    {% block stylesheets %}
        {{ parent() }}
        <link rel="stylesheet" href="{{ asset('css/custom-swagger-styles.css') }}">
    {% endblock stylesheets %}

    {% block javascripts %}
        {{ parent() }}
        <script type="text/javascript" src="{{ asset('js/custom-request-signer.js') }}"></script>
    {% endblock javascripts %}

You can have a look at the original template to see which blocks can be overridden.
