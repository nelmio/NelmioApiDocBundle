Frequently Asked Questions (FAQ)
================================

Sharing parameter configuration
-------------------------------

Q: I use the same value in multiple end points. How can I avoid duplicating the descriptions?

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

A: Optional path parameters are not supported by the swagger specification. The solution to this is to define two
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

The first action is redundant for Symfony, but adds all the relevant documentation for swagger.

Asset files not loaded
----------------------

Q: How do I fix 404 or 406 HTTP status on NelmioApiDocBundle assets files (css, js, images)?

A: The assets normally are installed by composer in the ``installAssets`` step.
   If you have not set up this step, you can manually execute this command:

.. code-block:: bash

    $ bin/console assets:install --symlink

