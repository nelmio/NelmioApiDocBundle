NelmioApiDocBundle
==================

The **NelmioApiDocBundle** bundle allows you to generate documentation in the
OpenAPI (Swagger) format and provides a sandbox to interactively browse the API documentation.

What's supported?
-----------------

This bundle supports *Symfony* route requirements, PHP annotations, `Swagger-Php`_ annotations,
`FOSRestBundle`_ annotations and apps using `Api-Platform`_.

.. _`Swagger-Php`: https://github.com/zircote/swagger-php
.. _`FOSRestBundle`: https://github.com/FriendsOfSymfony/FOSRestBundle
.. _`Api-Platform`: https://github.com/api-platform/api-platform

For models, it supports the Symfony serializer and the JMS serializer.

Migrate from 2.x to 3.0
-----------------------

`To migrate from 2.x to 3.0, just follow our guide.`__

__ https://github.com/nelmio/NelmioApiDocBundle/blob/master/UPGRADE-3.0.md

Installation
------------

Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

.. code-block:: bash

    $ composer require nelmio/api-doc-bundle

.. note::

    If you're not using Flex, then add the bundle to your kernel::

        class AppKernel extends Kernel
        {
            public function registerBundles()
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

        # app/config/routing.yml
        app.swagger_ui:
            path: /api/doc
            methods: GET
            defaults: { _controller: nelmio_api_doc.controller.swagger_ui }

    If you also want to expose it in JSON, register this route:

    .. code-block:: yaml

        # app/config/routing.yml
        app.swagger:
            path: /api/doc.json
            methods: GET
            defaults: { _controller: nelmio_api_doc.controller.swagger }

    As you just installed the bundle, you'll likely see routes you don't want in
    your documentation such as `/_profiler/`. To fix this, you can filter the
    routes that are documented by configuring the bundle:

    .. code-block:: yaml

        # app/config/config.yml
        nelmio_api_doc:
            areas:
                path_patterns: # an array of regexps
                    - ^/api(?!/doc$)

    .. versionadded:: 3.1

        The ``areas`` config option was added in version 3.1. You had to use ``routes`` in 3.0 instead.

How does this bundle work?
--------------------------

It generates you a swagger documentation from your symfony app thanks to
**Describers**. Each of these **Describers** extract infos from various sources.
For instance, one extract data from SwaggerPHP annotations, one from your
routes, etc.

If you configured the ``app.swagger_ui`` route above, you can browse your
documentation at `http://example.org/api/doc`.

Using the bundle
----------------

You can configure global information in the bundle configuration ``documentation.info`` section (take a look at
`the Swagger specification`_ to know the fields
available):

.. code-block:: yaml

    nelmio_api_doc:
        documentation:
            info:
                title: My App
                description: This is an awesome app!
                version: 1.0.0

.. _`The Swagger specification`: https://github.com/OAI/OpenAPI-Specification/blob/master/versions/2.0.md

.. note::

    If you're using Flex, this config is there by default. Don't forget to adapt it to your app!

To document your routes, you can use the SwaggerPHP annotations and the
``Nelmio\ApiDocBundle\Annotation\Model`` annotation in your controllers::

    namespace AppBundle\Controller;

    use AppBundle\Entity\User;
    use AppBundle\Entity\Reward;
    use Nelmio\ApiDocBundle\Annotation\Model;
    use Swagger\Annotations as SWG;
    use Symfony\Component\Routing\Annotation\Route;

    class UserController
    {
        /*
         * List the rewards of the specified user.
         *
         * This call takes into account all confirmed awards, but not pending or refused awards.
         *
         * @Route("/api/{user}/rewards", methods={"GET"})
         * @SWG\Response(
         *     response=200,
         *     description="Returns the rewards of an user",
         *     @SWG\Schema(
         *         type="array",
         *         @Model(type=Reward::class, groups={"full"})
         *     )
         * )
         * @SWG\Parameter(
         *     name="order",
         *     in="query",
         *     type="string",
         *     description="The field used to order rewards"
         * )
         * @SWG\Tag(name="rewards")
         */
        public function fetchUserRewardsAction(User $user)
        {
            // ...
        }
    }

The normal PHP docblock for the controller method is used for the summary and description.

Use models
----------

As shown in the example above, the bundle provides the ``@Model`` annotation.
When you use it, the bundle will deduce your model properties.

.. note::

    A model can be a Symfony form type, a Doctrine ORM entity or a general PHP object.

It has two options:

* ``type`` to specify your model's type::

    /**
     * @SWG\Response(
     *     response=200,
     *     @Model(type=User::class)
     * )
     */

* ``groups`` to specify the serialization groups used to (de)serialize your model::

    /**
     * @SWG\Response(
     *     response=200,
     *     @Model(type=User::class, groups={"non_sensitive_data"})
     * )
     */

.. caution::

    The ``@Model`` annotation acts like a ``@Schema`` annotation. If you nest it with a ``@Schema`` annotation, the bundle will consider that
    you're documenting an array of models.

    For instance, the following example::

        /**
         * @SWG\Response(
         *   response="200",
         *   description="Success",
         *   @SWG\Schema(@Model(type=User::class))
         * )
         */
        public function getUserAction()
        {
        }

    will produce:

    .. code-block:: yaml

        # ...
        responses:
            200:
                schema:
                    items: { $ref: '#/definitions/User' }

    while you probably expected:

    .. code-block:: yaml

        # ...
        responses:
            200:
                schema: { $ref: '#/definitions/User' }

    To obtain the output you expected, remove the ``@Schema`` annotation::

        /**
         * @SWG\Response(
         *   response="200",
         *   description="Success",
         *   @Model(type=User::class)
         * )
         */
        public function getUserAction()
        {
        }

If you're not using the JMS Serializer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The `Symfony PropertyInfo component`_ is used to describe your models. It supports doctrine annotations, type hints,
and even PHP doc blocks as long as you required the ``phpdocumentor/reflection-docblock`` library. It does also support
serialization groups when using the Symfony serializer.

If you're using the JMS Serializer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The metadata of the JMS serializer are used by default to describe your
models. Additional information is extracted from the PHP doc block comment,
but the property types must be specified in the JMS annotations.

In case you prefer using the `Symfony PropertyInfo component`_ (you
won't be able to use JMS serialization groups), you can disable JMS serializer
support in your config:

.. code-block:: yaml

    nelmio_api_doc:
        models: { use_jms: false }

Learn more
----------

If you need more complex features, take a look at:

.. toctree::
    :maxdepth: 1

    areas

.. _`Symfony PropertyInfo component`: https://symfony.com/doc/current/components/property_info.html
