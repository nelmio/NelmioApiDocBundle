Alternative Names
=================

NelmioApiDoc generates automatically the model names but the ``nelmio_api_doc.models.names`` option allows to
customize the names for some models.

Configuration
-------------

You can define alternative names for each group and area combination:

.. code-block:: yaml

    nelmio_api_doc:
        models:
            names:
                - { alias: MainUser,        type: App\Entity\User}
                - { alias: MainUser_light,  type: App\Entity\User, groups: [light] }
                - { alias: MainUser_secret, type: App\Entity\User, areas: [private] }
                - { alias: MainUser,        type: App\Entity\User, groups: [standard], areas: [private] }


In this case the class ``App\Entity\User`` will be named:

- ``MainUser`` in the default area and default group
- ``MainUser_light`` in the default area when the group is equal to ``light``
- ``MainUser_secret`` in the ``private`` area
- ``MainUser_light`` in the ``private`` area when the group is equal to ``light``
