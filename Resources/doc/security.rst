Security
========

A default security policy can be added in ``nelmio_api_doc.documentation.security``

.. code-block:: yaml

    nelmio_api_doc:
        documentation:
            components:
                securitySchemes:
                    Bearer:
                        type: http
                        scheme: bearer
                    ApiKeyAuth:
                        type: apiKey
                        in: header
                        name: X-API-Key
            security:
                - Bearer: []

This will add the Bearer security policy to all registered paths.

Overriding Specific Paths
-------------------------

The security policy can be overridden for a path using the ``Security`` annotation/attribute.

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @Security(name="ApiKeyAuth")
         */

    .. code-block:: php-attributes

        #[Security(name: "ApiKeyAuth")]

Notice at the bottom of the docblock is a ``Security`` annotation/attribute with a name of `ApiKeyAuth`. This will override the global security policy to only accept the ``ApiKeyAuth`` policy for this path.

You can also completely remove security from a path by providing ``Security`` with a name of ``null``.

.. configuration-block::

    .. code-block:: php-annotations

        /**
         * @Security(name=null)
         */

    .. code-block:: php-attributes

        #[Security(name: null)]
