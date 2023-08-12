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

Modifying the generated documentation can easily by done in two ways, by::
- Customizing the documentation of an object's property (``#[OA\Property]`` attribute)
- Customizing the documentation of a query parameter (``#[OA\Parameter]`` attribute)

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

        use AppBundle\UserQuery;
        use AppBundle\UserDTO;
        use Nelmio\ApiDocBundle\Annotation\Model;
        use Nelmio\ApiDocBundle\Annotation\Security;
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

Disclaimer
----------------------

Make sure to use at least php 8 (annotations) to make use of this functionality

.. _`Symfony MapQueryString`: https://symfony.com/doc/current/controller.html#mapping-the-whole-query-string
.. _`Symfony MapQueryParameter`: https://symfony.com/doc/current/controller.html#mapping-query-parameters-individually
.. _`Symfony MapRequestPayload`: https://symfony.com/doc/current/controller.html#mapping-request-payload
