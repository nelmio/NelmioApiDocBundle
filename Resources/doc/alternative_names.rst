Alternative Names
=================

NelmioApiDoc generates automatically the model names but the ``nelmio_api_doc.models.names`` option allows to
customize the names for some models.

Configuration
-------------

You can define alternative names for each group and area combination, the last matching rule is used:

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
- ``MainUser_light`` for the ``private`` area when the group is equal to ``light``
