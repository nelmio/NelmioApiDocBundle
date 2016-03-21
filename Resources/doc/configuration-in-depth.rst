Configuration In-Depth
======================

API Name
--------

You can specify your own API name:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        name: My API

Authentication Methods
----------------------

You can choose between different authentication methods:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        sandbox:
            authentication:
                delivery: header
                name:     X-Custom

    # app/config/config.yml
    nelmio_api_doc:
        sandbox:
            authentication:
                delivery: query
                name:     param

    # app/config/config.yml
    nelmio_api_doc:
        sandbox:
            authentication:
                delivery: http
                type:     basic # or bearer

When choosing an ``http`` delivery, ``name`` defaults to ``Authorization``, and
the header value will automatically be prefixed by the corresponding type (ie.
``Basic`` or ``Bearer``).

Section Exclusion
-----------------

You can specify which sections to exclude from the documentation generation:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        exclude_sections: ["privateapi", "testapi"]

Note that ``exclude_sections`` will literally exclude a section from your api
documentation. It's possible however to create multiple views by specifying the
``views`` parameter within the ``@ApiDoc`` annotations. This allows you to move
private or test methods to a complete different view of your documentation
instead.

Parsers
-------

By default, all registered parsers are used, but sometimes you may want to
define which parsers you want to use. The ``parsers`` attribute is used to
configure a list of parsers that will be used::

    output={
        "class"   = "Acme\Bundle\Entity\User",
        "parsers" = {
            "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
            "Nelmio\ApiDocBundle\Parser\ValidationParser"
        }
    }

In this case the parsers ``JmsMetadataParser`` and ``ValidationParser`` are used
to generate returned data. This feature also works for both the ``input`` and
``output`` properties.

Moreover, the bundle provides a way to register multiple ``input`` parsers. The
first parser that can handle the specified input is used, so you can configure
their priorities via container tags. Here's an example parser service
registration:

.. code-block:: yaml

    # app/config/config.yml
    services:
        mybundle.api_doc.extractor.custom_parser:
            class: MyBundle\Parser\CustomDocParser
            tags:
                - { name: nelmio_api_doc.extractor.parser, priority: 2 }

MOTD
----

You can also define your own motd content (above methods list). All you have to
do is add to configuration:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        # ...
        motd:
            template: AcmeApiBundle::Components/motd.html.twig

Caching
-------

It is a good idea to enable the internal caching mechanism on production:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        cache:
            enabled: true

You can define an alternate location where the ApiDoc configurations are to be
cached:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        cache:
            enabled: true
            file: "/tmp/symfony-app/%kernel.environment%/api-doc.cache"
