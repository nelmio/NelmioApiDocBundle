NelmioApiDocBundle
==================

The **NelmioApiDocBundle** bundle allows you to generate documentation in the
OpenAPI (Swagger) format and provides a sandbox to interactively experiment with the API.

What's supported?
-----------------

This bundle supports *Symfony* route requirements, PHP annotations, `Swagger-Php`_ annotations,
`FOSRestBundle`_ annotations and apps using `Api-Platform`_.

.. _`Swagger-Php`: https://github.com/zircote/swagger-php
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`Api-Platform`: https://github.com/api-platform/api-platform

For models, it supports the `Symfony serializer`_ , the `JMS serializer`_ and the `willdurand/Hateoas`_ library.
It does also support `Symfony form`_ types.

Attributes are supported from version 4.7 and PHP 8.1.

Migrate from 3.x to 4.0
-----------------------

`To migrate from 3.x to 4.0, follow our guide.`__

__ https://github.com/nelmio/NelmioApiDocBundle/blob/master/UPGRADE-4.0.md

Installation
------------

Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

.. code-block:: bash

    $ composer require nelmio/api-doc-bundle

.. note::

    If you're not using Flex, then add the bundle to your kernel::

        class AppKernel extends Kernel
        {
            public function registerBundles(): iterable
            {
                $bundles = [
                    // ...

                    new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
                ];

                // ...
            }
        }

    To browse your documentation with Swagger UI, register the following route:

    .. code-block:: yaml

        # config/routes.yaml
        app.swagger_ui:
            path: /api/doc
            methods: GET
            defaults: { _controller: nelmio_api_doc.controller.swagger_ui }

    If you also want to expose it in JSON, register this route:

    .. code-block:: yaml

        # config/routes.yaml
        app.swagger:
            path: /api/doc.json
            methods: GET
            defaults: { _controller: nelmio_api_doc.controller.swagger }

    As you just installed the bundle, you'll likely see routes you don't want in
    your documentation such as ``/_profiler/``. To fix this, you can filter the
    routes that are documented by configuring the bundle:

    .. code-block:: yaml

        # config/packages/nelmio_api_doc.yaml
        nelmio_api_doc:
            areas:
                path_patterns: # an array of regexps
                    - ^/api(?!/doc$)
                host_patterns:
                    - ^api\.

How does this bundle work?
--------------------------

It generates an OpenAPI documentation from your Symfony app thanks to
**Describers**. One extracts data from SwaggerPHP annotations, one from your
routes, etc.

If you configured the ``app.swagger_ui`` route above, you can browse your
documentation at `http://example.org/api/doc`.

Using the bundle
----------------

You can configure global information in the bundle configuration ``documentation.info`` section (take a look at
`the OpenAPI 3.0 specification (formerly Swagger)`_ to know the available fields). 

.. code-block:: yaml

    nelmio_api_doc:
        documentation:
            servers:
              - url: http://api.example.com/unsafe
                description: API over HTTP
              - url: https://api.example.com/secured
                description: API over HTTPS
            info:
                title: My App
                description: This is an awesome app!
                version: 1.0.0
            components:
                securitySchemes:
                    Bearer:
                        type: http
                        scheme: bearer
                        bearerFormat: JWT
            security:
                - Bearer: []

.. _`the OpenAPI 3.0 specification (formerly Swagger)`: https://swagger.io/docs/specification

.. note::

    If you're using Flex, this config is there by default under ``config/packages/nelmio_api_doc.yaml``. Don't forget to adapt it to your app!

.. tip::

    This configuration field can more generally be used to store your documentation as yaml. 
    You may find in the ``.yaml`` files from `SwaggerPHP examples`_. 

To document your routes, you can use the SwaggerPHP annotations and the
``Nelmio\ApiDocBundle\Annotation\Model`` annotation in your controllers::

.. configuration-block::

    .. code-block:: php-annotations
    
        namespace AppBundle\Controller;

        use AppBundle\Entity\User;
        use AppBundle\Entity\Reward;
        use Nelmio\ApiDocBundle\Annotation\Model;
        use Nelmio\ApiDocBundle\Annotation\Security;
        use OpenApi\Annotations as OA;
        use Symfony\Component\Routing\Annotation\Route;

        class UserController
        {
            /**
             * List the rewards of the specified user.
             *
             * This call takes into account all confirmed awards, but not pending or refused awards.
             *
             * @Route("/api/{user}/rewards", methods={"GET"})
             * @OA\Response(
             *     response=200,
             *     description="Returns the rewards of an user",
             *     @OA\JsonContent(
             *        type="array",
             *        @OA\Items(ref=@Model(type=Reward::class, groups={"full"}))
             *     )
             * )
             * @OA\Parameter(
             *     name="order",
             *     in="query",
             *     description="The field used to order rewards",
             *     @OA\Schema(type="string")
             * )
             * @OA\Tag(name="rewards")
             * @Security(name="Bearer")
             */
            public function fetchUserRewardsAction(User $user)
            {
                // ...
            }
        }
    
    .. code-block:: php-attributes
    
        namespace AppBundle\Controller;

        use AppBundle\Entity\User;
        use AppBundle\Entity\Reward;
        use Nelmio\ApiDocBundle\Annotation\Model;
        use Nelmio\ApiDocBundle\Annotation\Security;
        use OpenApi\Attributes as OA;
        use Symfony\Component\Routing\Annotation\Route;

        class UserController
        {
            /**
             * List the rewards of the specified user.
             *
             * This call takes into account all confirmed awards, but not pending or refused awards.
             */
            #[Route('/api/{user}/rewards', methods=['GET'])]
            #[OA\Response(
                response: 200,
                description: 'Returns the rewards of an user',
                content: new OA\JsonContent(
                    type: 'array', 
                    items: new OA\Items(ref: new Model(type: AlbumDto::class, groups: ['full']))
                )
            )]
            #[OA\Parameter(
                name: 'order',
                in: 'query',
                description: 'The field used to order rewards',
                schema: new OA\Schema(type: 'string')
            )]
            #[OA\Tag(name: 'rewards')]
            #[Security(name: 'Bearer')]
            public function fetchUserRewardsAction(User $user)
            {
                // ...
            }
        }
    

The normal PHPdoc block on the controller method is used for the summary and description.

.. tip::

    Examples of using the annotations can be found in `SwaggerPHP examples`_.
    However, unlike in those examples, when using this bundle you don't need to specify paths and you can easily document models as well as some
    other properties described below as they can be automatically be documented using the Symfony integration. 

Use models
----------

As shown in the example above, the bundle provides the ``@Model`` annotation.
Use it instead of a definition reference and the bundle will deduce your model properties.

.. note::

    A model can be a Symfony form type, a Doctrine ORM entity or a general PHP object.

This annotation has two options:

* ``type`` to specify your model's type::

.. configuration-block::

    .. code-block:: php-annotations
    
        /**
         * @OA\Response(
         *     response=200,
         *     @Model(type=User::class)
         * )
         */

    .. code-block:: php-attributes
    
        #[OA\Response(
            response: 200,
            description: 'Successful response',
            content: new Model(type: User::class)
        )]

* ``groups`` to specify the serialization groups used to (de)serialize your model::


.. configuration-block::

    .. code-block:: php-annotations

         /**
         * @OA\Response(
         *     response=200,
         *     @Model(type=User::class, groups={"non_sensitive_data"})
         * )
         */

    .. code-block:: php-attributes
    
        #[OA\Response(
            response: 200,
            description: 'Successful response',
            content: new Model(type: User::class, groups: ['non_sensitive_data'])
        )]

.. tip::

     When used at the root of ``@OA\Response`` and ``@OA\Parameter``, ``@Model`` is automatically nested
     in a ``@OA\Schema``.

     The media type defaults to ``application/json``.

     To use ``@Model`` directly within a ``@OA\Schema``, ``@OA\Items`` or ``@OA\Property``, you have to use the ``$ref`` field::

.. configuration-block::

    .. code-block:: php-annotations

         /**
          * @OA\Response(
          *     @OA\JsonContent(ref=@Model(type=User::class))
          * )
          *
          * or
          *
          * @OA\Response(@OA\XmlContent(
          *     @OA\Schema(type="object",
          *         @OA\Property(property="foo", ref=@Model(type=FooClass::class))
          *     )
          * ))
          */

    .. code-block:: php-attributes

        #[OA\Response(
            content: new OA\JsonContent(ref: new Model(type: User::class))
        )]
         /**
          * or
          */
        #[OA\Response(
            content: new OA\XmlContent(example: new OA\Schema(
                type: 'object',
                properties: [
                    new OA\Property(property: 'foo', ref: new Model(type: FooClass::class))
                ]
            ))
        )]

Symfony Form types
~~~~~~~~~~~~~~~~~~

You can customize the documentation of a form field using the ``documentation`` option::

    $builder->add('username', TextType::class, [
        'documentation' => [
            'type' => 'string', // would have been automatically detected in this case
            'description' => 'Your username.',
        ],
    ]);

See the `OpenAPI 3.0 specification`__ to see all the available fields of the ``documentation`` option (it accepts the same fields as the OpenApi ``Property`` object).

__ https://swagger.io/specification/


General PHP objects
~~~~~~~~~~~~~~~~~~~

.. tip::

    **If you're not using the JMS Serializer**, the `Symfony PropertyInfo component`_ is used to describe your models.
    It supports doctrine annotations, type hints, and even PHP doc blocks.
    It does also support serialization groups when using the Symfony serializer.

    **If you're using the JMS Serializer**, the metadata of the JMS serializer are used by default to describe your
    models. Additional information is extracted from the PHP doc block comment,
    but the property types must be specified in the JMS annotations.
    
    NOTE: If you are using serialization contexts (e.g. Groups) each permutation will be treated as a separate Path. For example if you have the following two variations defined in different places in your code:
    
    .. code-block:: php

        /**
         * A nested serializer property with no context group
         *
         * @JMS\VirtualProperty
         * @JMS\Type("ArrayCollection<App\Response\ItemResponse>")
         * @JMS\Since("1.0")
         *
         * @return Collection|ItemResponse[]
         */
        public function getItems(): Collection|array
        {
            return $this->items;
        }

    .. code-block:: php

        .. configuration-block::

            .. code-block:: php-annotations

                @OA\Schema(ref=@Model(type="App\Response\ItemResponse", groups=["Default"])),

            .. code-block:: php-attributes

                #[OA\Schema(ref: new Model(type: App\Response\ItemResponse::class, groups: ['Default']))]

    It will generate two different component schemas (ItemResponse, ItemResponse2), even though Default and blank are the same. This is by design.

    In case you prefer using the `Symfony PropertyInfo component`_ (you
    won't be able to use JMS serialization groups), you can disable JMS serializer
    support in your config:

    .. code-block:: yaml

        nelmio_api_doc:
            models: { use_jms: false }

    When using the JMS serializer combined with `willdurand/Hateoas`_ (and the `BazingaHateoasBundle`_),
    HATEOAS metadata are automatically extracted

If you want to customize the documentation of an object's property, you can use ``@OA\Property``::


.. configuration-block::

    .. code-block:: php-annotations
            
        use Nelmio\ApiDocBundle\Annotation\Model;
        use OpenApi\Annotations as OA;

        class User
        {
            /**
             * @var int
             * @OA\Property(description="The unique identifier of the user.")
             */
            public $id;

            /**
             * @OA\Property(type="string", maxLength=255)
             */
            public $username;

            /**
             * @OA\Property(ref=@Model(type=User::class))
             */
            public $friend;

            /**
             * @OA\Property(description="This is my coworker!")
             */
            public setCoworker(User $coworker) {
                // ...
            }
        }

    .. code-block:: php-attributes
            
        use Nelmio\ApiDocBundle\Annotation\Model;
        use OpenApi\Attributes as OA;

        class User
        {
            /**
             * @var int
             */
            #[OA\Property(description: 'The unique identifier of the user.')]
            public $id;

            #[OA\Property(type: 'string', maxLength: 255)]
            public $username;

            #[OA\Property(ref: new Model(type: User::class))]
            public $friend;

            #[OA\Property(description: 'This is my coworker!')]
            public setCoworker(User $coworker) {
                // ...
            }
        }

See the `OpenAPI 3.0 specification`__ to see all the available fields of ``@OA\Property``.

__ https://swagger.io/specification/

Learn more
----------

If you need more complex features, take a look at:

.. toctree::
    :maxdepth: 1

    areas
    alternative_names
    customization
    commands
    faq
    security

.. _`SwaggerPHP examples`: https://github.com/zircote/swagger-php/tree/master/Examples
.. _`Symfony PropertyInfo component`: https://symfony.com/doc/current/components/property_info.html
.. _`willdurand/Hateoas`: https://github.com/willdurand/Hateoas
.. _`BazingaHateoasBundle`: https://github.com/willdurand/BazingaHateoasBundle
.. _`JMS serializer`: https://jmsyst.com/libs/serializer
.. _`Symfony form`: https://symfony.com/doc/current/forms.html
.. _`Symfony serializer`: https://symfony.com/doc/current/components/serializer.html
