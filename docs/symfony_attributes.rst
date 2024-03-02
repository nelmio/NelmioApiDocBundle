Symfony attributes
================================

NelmioApiDocBundle has the ability to automatically create documentation from **symfony** controller attributes.

MapQueryString
-------------------------------

Using the `Symfony MapQueryString`_ attribute allows NelmioApiDocBundle to automatically generate your query parameter documentation for your endpoint from your object.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapQueryString` attribute was introduced in Symfony 6.3.

Modify generated documentation
~~~~~~~

Modifying the generated documentation can easily by done in two ways, by:
* Customizing the documentation of an object's property (``#[OA\Property]`` attribute)
* Customizing the documentation of a query parameter (``#[OA\Parameter]`` attribute)

Customizing the documentation of a specific query parameter can be done by adding the ``#[OA\Parameter]`` attribute to your controller method.
Make sure that the ``in`` property is set to ``'query'`` and that the ``name`` property is set to the object's property name which you want to customize.

    .. code-block:: php-attributes

        #[OA\Parameter(
            name: 'id',
            description: 'Some additional parameter description',
            in: 'query',
        )]

MapQueryParameter
-------------------------------

Using the `Symfony MapQueryParameter`_ attribute allows NelmioApiDocBundle to automatically generate your query parameter documentation for your endpoint.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapQueryParameter` attribute was introduced in Symfony 6.3.


Modify generated documentation
~~~~~~~

Customizing the documentation of the query parameter can be done by adding the ``#[OA\Parameter]`` attribute to your controller method.
Make sure that the ``in`` property is set to ``'query'`` and that the ``name`` property is set to the name of the controller method parameter.

    .. code-block:: php-attributes

        #[OA\Parameter(
            name: 'id',
            description: 'Some additional parameter description',
            in: 'query',
        )]

MapRequestPayload
-------------------------------

Using the `Symfony MapRequestPayload`_ attribute allows NelmioApiDocBundle to automatically generate your request body documentation for your endpoint.

.. versionadded:: 6.3

    The :class:`Symfony\\Component\\HttpKernel\\Attribute\\MapRequestPayload` attribute was introduced in Symfony 6.3.


Modify generated documentation
~~~~~~~

Customizing the documentation of the request body can be done by adding the ``#[OA\RequestBody]`` attribute to your controller method.

    .. code-block:: php-attributes

        #[OA\RequestBody(
            groups: ["create"],
        )

Complete example
----------------------

    .. code-block:: php-attributes

        class UserQuery
        {
            public int $userId;
        }

    .. code-block:: php-attributes

        use Symfony\Component\Serializer\Annotation\Groups;
        use Symfony\Component\Validator\Constraints as Assert;

        class UserDto
        {
            #[Groups(["default", "create", "update"])]
            #[Assert\NotBlank(groups: ["default", "create"])]
            public string $username;
        }

    .. code-block:: php-attributes

        namespace AppBundle\Controller;

        use AppBundle\UserDTO;
        use AppBundle\UserQuery;
        use OpenApi\Attributes as OA;
        use Symfony\Component\Routing\Annotation\Route;

        class UserController
        {
            /**
             * Find user with MapQueryString.
             */
            #[Route('/api/users', methods: ['GET'])]
            #[OA\Parameter(
                name: 'userId',
                description: 'Id of the user to find',
                in: 'query',
            )]
            public function findUser(#[MapQueryString] UserQuery $userQuery)
            {
                // ...
            }

            /**
             * Find user with MapQueryParameter.
             */
            #[Route('/api/users/v2', methods: ['GET'])]
            #[OA\Parameter(
                name: 'userId',
                description: 'Id of the user to find',
                in: 'query',
            )]
            public function findUserV2(#[MapQueryParameter] int $userId)
            {
                // ...
            }

            /**
             * Create a new user.
             */
            #[Route('/api/users', methods: ['POST'])]
            #[OA\RequestBody(
                groups: ['create'],
            )]
            public function createUser(#[MapRequestPayload] UserDTO $user)
            {
                // ...
            }
        }

Customization
----------------------

Imagine you want to add, modify, or remove some documentation for a route argument. For that you will have to create your own describer which implements the `RouteArgumentDescriberInterface`_ interface.

Register your route argument describer
~~~~~~~

Before you can use your custom describer you must register it in your route argument describer as a service and tag it with ``nelmio_api_doc.route_argument_describer``.
Services implementing the `RouteArgumentDescriberInterface`_ interface are automatically detected and used by NelmioApiDocBundle.

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\Describer\CustomRouteArgumentDescriber:
                tags:
                    - { name: nelmio_api_doc.route_argument_describer }

    .. code-block:: xml

        <!-- config/services.xml -->
        <service id="App\Describer\CustomRouteArgumentDescriber">
            <tag name="nelmio_api_doc.route_argument_describer"/>
        </service>

    .. code-block:: php

        // config/services.php
        use App\Describer\CustomRouteArgumentDescriber;

        return function (ContainerConfigurator $container) {
            $container->services()
                ->set(CustomRouteArgumentDescriber::class)
                ->tag('nelmio_api_doc.route_argument_describer')
            ;
        };

Disclaimer
----------------------

Make sure to use at least php 8.1 (attribute support) to make use of this functionality.

.. _`Symfony MapQueryString`: https://symfony.com/doc/current/controller.html#mapping-the-whole-query-string
.. _`Symfony MapQueryParameter`: https://symfony.com/doc/current/controller.html#mapping-query-parameters-individually
.. _`Symfony MapRequestPayload`: https://symfony.com/doc/current/controller.html#mapping-request-payload
.. _`RouteArgumentDescriberInterface`: https://github.com/DjordyKoert/NelmioApiDocBundle/blob/master/RouteDescriber/RouteArgumentDescriber/RouteArgumentDescriberInterface.php
