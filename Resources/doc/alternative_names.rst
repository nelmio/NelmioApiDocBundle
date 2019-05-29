Alternative Names
=================

NelmioApiDoc automatically generates the model names but the ``nelmio_api_doc.models.names`` option allows to
customize the names for some models.

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

    This allows to use normal references instead of ``@Model``. Notably, you can specify
    the groups used for a model once in config and then refer to its alternative name:

    .. code-block:: yaml

        nelmio_api_doc:
            models:
                names: [ { alias: MyModel, type: App\MyModel, groups: [light] }]

    .. code-block:: php

        class HomeController
        {
            /**
             * @SWG\Response(response=200, @SWG\Schema(ref="#/definitions/MyModel"))
             */
            public function indexAction()
            {
            }
        }
