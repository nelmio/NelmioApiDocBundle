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

Automatically Generated Security Definitions
---------------------------------------------

NelmioApiDocBundle can automatically generate security definitions based on the ``#[IsGranted]`` attribute.
You can configure the security scheme(s) per area in your area configuration.

See the `security documentation on swagger`_ for more information on authentication schemes.

.. versionadded:: 5.2

    The possibility to automatically generate security definitions based on the ``#[IsGranted]`` attribute was added in version 5.2.

.. code-block:: yaml

        nelmio_api_doc:
            # ...

            areas:
                default:
                    security:
                        ApiKeyAuth:
                            type: 'apiKey'
                            name: 'X-API-Key'
                            in: 'header'

Above is an example of security configuration for the ``default`` area. This will add the ``ApiKeyAuth`` security scheme to all registered paths in the ``default`` area.

.. tabs:: Controller examples

    .. tab:: Controller with #[IsGranted]

        An example of a controller using the ``#[IsGranted]`` attribute to define security scopes.

        .. code-block:: php-attributes

            use Symfony\Component\Routing\Annotation\Route;
            use Symfony\Component\Security\Http\Attribute\IsGranted;

            #[IsGranted(attribute: 'read')]
            class UserController
            {
                #[Route('/api/users', methods: ['POST'])]
                #[IsGranted(attribute: 'write')]
                public function createUser()
                {
                    // ...
                }
            }

        .. code-block:: json

            {
                "paths": {
                    "/api/users": {
                        "post": {
                            "security": [
                                {
                                    "ApiKeyAuth": [
                                        "read",
                                        "write"
                                    ]
                                }
                            ]
                        }
                    }
                },
                "components": {
                     "securitySchemes": {
                          "ApiKeyAuth": {
                                "type": "apiKey",
                                "name": "X-API-KEY",
                                "in": "header"
                            }
                      }
                }
            }

    .. tab:: Controller without #[IsGranted] (No security)

        An example of a controller without the ``#[IsGranted]`` attribute.

        .. code-block:: php-attributes

            use Symfony\Component\Routing\Annotation\Route;

            class UserController
            {
                #[Route('/api/users', methods: ['POST'])]
                public function createUser()
                {
                    // ...
                }
            }

        .. code-block:: json

            {
                "paths": {
                    "/api/users": {
                        "post": {
                            "security": [
                                {
                                    "ApiKeyAuth": []
                                }
                            ]
                        }
                    }
                },
                "components": {
                     "securitySchemes": {
                          "ApiKeyAuth": {
                                "type": "apiKey",
                                "name": "X-API-KEY",
                                "in": "header"
                            }
                      }
                }
            }

Overriding Generated Security Definitions
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

Want to override the generated security definition? You can do that by using the ``#[Security]`` attribute.

        .. code-block:: php-attributes

            use Nelmio\ApiDocBundle\Attribute\Security;
            use Symfony\Component\Routing\Annotation\Route;
            use Symfony\Component\Security\Http\Attribute\IsGranted;

            #[IsGranted(attribute: 'read')]
            class UserController
            {
                #[Route('/api/users', methods: ['POST'])]
                #[IsGranted(attribute: 'write')]
                #[Security(
                    name: 'BearerAuthCustom',
                    scopes: ['bearer:read'],
                )]
                public function createUser()
                {
                    // ...
                }
            }

        .. code-block:: json

            {
                "paths": {
                    "/api/users": {
                        "post": {
                            "security": [
                                {
                                    "BearerAuthCustom": [
                                        "bearer:read",
                                    ]
                                }
                            ]
                        }
                    }
                },
                "components": {
                     "securitySchemes": {
                          "ApiKeyAuth": {
                                "type": "apiKey",
                                "name": "X-API-KEY",
                                "in": "header"
                            }
                      }
                }
            }


Overriding Specific Paths
-------------------------

The security policy can be overridden for a path using the ``Security`` attribute.

.. configuration-block::

    .. code-block:: php-attributes

        #[Security(name: "ApiKeyAuth")]

Notice at the bottom of the docblock is a ``Security`` attribute with a name of `ApiKeyAuth`. This will override the global security policy to only accept the ``ApiKeyAuth`` policy for this path.

You can also completely remove security from a path by providing ``Security`` with a name of ``null``.

.. configuration-block::

    .. code-block:: php-attributes

        #[Security(name: null)]

.. _`security documentation on swagger`: https://swagger.io/docs/specification/v3_0/authentication/