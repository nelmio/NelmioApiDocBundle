Frequently Asked Questions (FAQ)
================================

Sharing parameter configuration
-------------------------------

Q: I use the same value in multiple endpoints. How can I avoid duplicating the descriptions?

A: You can configure ``definitions`` in the nelmio_api_doc configuration and then reference them:

.. code-block:: yaml

    # config/nelmio_api_doc.yml
    nelmio_api_doc:
        documentation:
            definitions:
                NelmioImageList:
                    description: "Response for some queries"
                    type: object
                    properties:
                        total:
                            type: integer
                            example: 42
                        items:
                            type: array
                            items:
                                $ref: "#/definitions/ImageMetadata"

.. code-block:: php

    // src/App/Controller/NelmioController.php

    /**
     * @SWG\Response(
     *     response=200,
     *     description="List of image definitions",
     *     @SWG\Schema(
     *       type="object",
     *       title="ListOperationsResponse",
     *       additionalProperties={"$ref": "#/definitions/NelmioImageList"}
     *     )
     */

Optional Path Parameters
------------------------

Q: I have a controller with an optional path parameter. In swagger-ui, the parameter is required - can I make it
optional? The controller might look like this::

    /**
     * Get all user meta or metadata for a specific field.
     *
     * @Route("/{user}/meta/{metaName}",
     *     methods={"GET"},
     *     name="get_user_metadata"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Json object with all user meta data or a json string with the value of the requested field"
     * )
     */
    public function getAction(string $user, string $metaName = null)
    {
        ...
    }

A: Optional path parameters are not supported by the OpenAPI specification. The solution to this is to define two
separate actions in your controller. For example::

    /**
     * Get all user meta data.
     *
     * @Route("/{user}/meta",
     *     methods={"GET"},
     *     name="get_user_metadata"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="Json hashmap with all user meta data",
     *     @SWG\Schema(
     *        type="object",
     *        example={"foo": "bar", "hello": "world"}
     *     )
     *
     * )
     */
    public function cgetAction(string $user)
    {
        return $this->getAction($user, null);
    }

    /**
     * Get user meta for a specific field.
     *
     * @Route("/{user}/meta/{metaName}",
     *     methods={"GET"},
     *     name="get_user_metadata_single"
     * )
     *
     * @SWG\Response(
     *     response=200,
     *     description="A json string with the value of the requested field",
     *     @SWG\Schema(
     *          type="string"
     *     )
     * )
     */
    public function getAction(string $user, string $metaName = null)
    {
        ...
    }

The first action is redundant for Symfony, but adds all the relevant documentation for the OpenAPI specification.

Asset files not loaded
----------------------

Q: How do I fix 404 or 406 HTTP status on NelmioApiDocBundle assets files (css, js, images)?

A: The assets normally are installed by composer if any command event (usually ``post-install-cmd`` or
``post-update-cmd``) triggers the ``ScriptHandler::installAssets`` script.
If you have not set up this script, you can manually execute this command:

.. code-block:: bash

    $ bin/console assets:install --symlink

Re-add Google Fonts
-------------------

Q: How can I change the font for the UI?

A: We removed the google fonts in 3.3 to avoid the external request for GDPR reasons. To change the font, you can :doc:`customize the template <customization>` to add this style information:

.. code-block:: twig

    {# templates/bundles/NelmioApiDocBundle/SwaggerUI/index.html.twig #}
    
    {#
       To avoid a "reached nested level" error an exclamation mark `!` has to be added
       See https://symfony.com/blog/new-in-symfony-3-4-improved-the-overriding-of-templates
    #}
    {% extends '@!NelmioApiDoc/SwaggerUi/index.html.twig' %}
    
    {% block stylesheets %}
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Code+Pro:300,600|Titillium+Web:400,600,700">
        {{ parent() }}
        <style type="text/css" rel="stylesheet">
            #formats {
                font-family: Open Sans,sans-serif;
            }

            .swagger-ui .opblock-tag,
            .swagger-ui .opblock .opblock-section-header label,
            .swagger-ui .opblock .opblock-section-header h4,
            .swagger-ui .opblock .opblock-summary-method,
            .swagger-ui .tab li,
            .swagger-ui .scheme-container .schemes>label,
            .swagger-ui .loading-container .loading:after,
            .swagger-ui .btn,
            .swagger-ui .btn.cancel,
            .swagger-ui select,
            .swagger-ui label,
            .swagger-ui .dialog-ux .modal-ux-content h4,
            .swagger-ui .dialog-ux .modal-ux-header h3,
            .swagger-ui section.models h4,
            .swagger-ui section.models h5,
            .swagger-ui .model-title,
            .swagger-ui .parameter__name,
            .swagger-ui .topbar a,
            .swagger-ui .topbar .download-url-wrapper .download-url-button,
            .swagger-ui .info .title small pre,
            .swagger-ui .scopes h2,
            .swagger-ui .errors-wrapper hgroup h4 {
                font-family: Open Sans,sans-serif!important;
            }
        </style>
    {% endblock stylesheets %}

Endpoints grouping
------------------

Q: Areas feature doesn't fit my needs. So how can I group similar endpoints of one or more controllers in a separate section in the documentation?

A: Use ``@SWG\Tag`` annotation.

.. code-block:: php

    /**
     * Class BookmarkController
     *
     * @SWG\Tag(name="Bookmarks")
     */
    class BookmarkController extends AbstractFOSRestController implements ContextPresetInterface
    {
        //...
    }
