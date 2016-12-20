Other Bundle Annotations
========================

This bundle will get information from the following other annotations:

* ``@FOS\RestBundle\Controller\Annotations\RequestParam`` - use as ``parameters``
* ``@FOS\RestBundle\Controller\Annotations\QueryParam`` - use as ``requirements``
  (when strict parameter is true), ``filters`` (when strict is false)
* ``@JMS\SecurityExtraBundle\Annotation\Secure`` - set ``authentication`` to true,
  ``authenticationRoles`` to the given roles
* ``@Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache`` - set ``cache``

PHPDoc
------

Actions marked as ``@deprecated`` will be marked as such in the interface.

JMS Serializer Features
-----------------------

The bundle has support for some of the JMS Serializer features and uses this
extra piece of information in the generated documentation.

Group Exclusion Strategy
------------------------

If your classes use `JMS Group Exclusion Strategy`_, you can specify which
groups to use when generating the documentation by using this syntax::

    input={
        "class"="Acme\Bundle\Entity\User",
        "groups"={"update", "public"}
    }

In this case the groups ``update`` and ``public`` are used. This feature also
works for the ``output`` property.

Versioning Objects
------------------

If your ``output`` classes use `versioning capabilities of JMS Serializer`_, the
versioning information will be automatically used when generating the
documentation.

Form Types Features
-------------------

Even if you use ``FormFactoryInterface::createNamed('', 'your_form_type')`` the
documentation will generate the form type name as the prefix for inputs
(``your_form_type[param]`` ... instead of just ``param``).

You can specify which prefix to use with the ``name`` key in the ``input``
section::

    input = {
     "class" = "your_form_type",
     "name" = ""
    }

You can also add some options to pass to the form. You just have to use the
``options`` key::

    input = {
     "class" = "your_form_type",
     "options" = {"method" = "PUT"},
    }

Using Your Own Annotations
--------------------------

If you have developed your own project-related annotations, and you want to
parse them to populate the ``ApiDoc``, you can provide custom handlers as
services. You just have to implement the
``Nelmio\ApiDocBundle\Extractor\HandlerInterface`` and tag it as
``nelmio_api_doc.extractor.handler``:

.. code-block:: yaml

    # app/config/config.yml
    services:
        mybundle.api_doc.extractor.my_annotation_handler:
            class: MyBundle\AnnotationHandler\MyAnnotationHandler
            tags:
                - { name: nelmio_api_doc.extractor.handler }

Look at the `built-in Handlers`_.

.. _`JMS Group Exclusion Strategy`: http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#creating-different-views-of-your-objects
.. _`versioning capabilities of JMS Serializer`: http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#versioning-objects
.. _`built-in Handlers`: https://github.com/nelmio/NelmioApiDocBundle/tree/master/Extractor/Handler
