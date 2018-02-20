Frequently Asked Questions (FAQ)
================================

* Q: I use ``@Model`` to document an operation and the bundle understands I want an array of models while I only want one.

  A: You most likely nested ``@Model`` in a ``@Schema`` annotation. The ``@Model`` annotation acts like a ``@Schema`` annotation, so
     when nested, the bundle considers that you're documenting an array of models.

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

* Q: How do I fix 404 or 406 HTTP status on NelmioApiDocBundle assets files (css, js, images)?

  A: Just execute this command to solve it:

  .. code-block:: bash

      $ bin/console assets:install --symlink
