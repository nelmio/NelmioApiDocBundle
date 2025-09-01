Alternative Names
=================

NelmioApiDoc automatically generates model names, but you can customize them using the
``nelmio_api_doc.models.names`` configuration option or the ``name`` property on the ``#[Model]`` attribute.

Configuration
-------------

You can define alternative names for each group and area combinations: when conflicts arises, the last matching rule will be used:

.. code-block:: yaml

    nelmio_api_doc:
        models:
            names:
                - { alias: MainUser,        type: App\Entity\User}
                - { alias: MainUser_light,  type: App\Entity\User, groups: [light] }
                - { alias: MainUser_secret, type: App\Entity\User, areas: [private] }
                - { alias: MainUser,        type: App\Entity\User, groups: [standard], areas: [private] }


In this case the class ``App\Entity\User`` will be aliased into:

- ``MainUser`` when no more detailed rules are specified
- ``MainUser_light`` when the group is equal to ``light``
- ``MainUser_secret`` for the ``private`` area
- ``MainUser`` for the ``private`` area when the group is equal to ``standard``

.. tip::

    This allows to use normal references instead of ``#[Model]``. Notably, you can specify
    the groups used for a model once in config and then refer to its alternative name:

    .. code-block:: yaml

        nelmio_api_doc:
            models:
                names: [ { alias: MyModel, type: App\MyModel, groups: [light] }]

    .. configuration-block::

        .. code-block:: php-attributes

            use OpenApi\Attributes as OA;

            class HomeController
            {
                #[OA\Response(response: 200, content: new OA\JsonContent(ref: "#/components/schemas/MyModel"))]
                public function indexAction()
                {
                }
            }

Using the Model attribute
-------------------------

You can also specify an alternative name directly within the ``#[Model]`` attribute by using the ``name`` property.
This is useful when you want to define a custom name for a model in a specific context.

.. configuration-block::

    .. code-block:: php-attributes

        use App\Entity\User;
        use Nelmio\ApiDocBundle\Attribute\Model;
        use OpenApi\Attributes as OA;

        class UserController
        {
            #[OA\Response(
                response: 200,
                description: "Success",
                content: new Model(type: User::class, name: "User_CustomName")
            )]
            public function getUser()
            {
                // ...
            }
        }

This will register the ``User`` model with the name ``User_CustomName`` in the OpenAPI documentation.

.. versionadded:: 5.6

    The possibility to define alternative names for models through the ``#[Model]`` attribute was added in version 5.6.
