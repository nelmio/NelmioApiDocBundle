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
                MainUser: App\Entity\User
                MainUser_light: { type: App\Entity\User, groups: [light]}
                MainUser_secret: { type: App\Entity\User, groups: [light], areas: [private]}


In this case the class ``App\Entity\User`` will be named:

- ``MainUser`` in the default area and default group
- ``MainUser_light`` in the default area when the group is equal to ``light``
- ``MainUser_secret`` in the ``private`` area when the group is equal to ``light``
