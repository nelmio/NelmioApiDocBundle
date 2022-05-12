Commands
========

A command is provided in order to dump the documentation in ``json``, ``yaml`` or ``html``.

.. code-block:: bash

    $ php bin/console nelmio:apidoc:dump [--format="..."]

The ``--format`` option allows to choose the format (default is: ``json``).

By default, the generated JSON will be pretty-formatted.  If you want to generate a json
without whitespace, use the ``--no-pretty`` option.

.. code-block:: bash

    $ php bin/console nelmio:apidoc:dump --format=json > json-pretty-formatted.json
    $ php bin/console nelmio:apidoc:dump --format=json --no-pretty > json-no-pretty.json

Every format can override API url. Useful if static documentation is not hosted on API url:

.. code-block:: bash

    $ php bin/console nelmio:apidoc:dump --format=yaml --server-url "http://example.com/api" > api.yaml

For example to generate a static version of your documentation you can use:

.. code-block:: bash

    $ php bin/console nelmio:apidoc:dump --format=html > api.html

By default, the generated HTML will add the sandbox feature.
If you want to generate a static version of your documentation without sandbox,
or configure UI configuration, use the ``--html-config`` option.

- ``assets_mode`` - `cdn` loads assets from CDN, `offline` inlines assets
- ``server_url`` - API url, useful if static documentation is not hosted on API url
- ``swagger_ui_config`` - `configure Swagger UI`_
    - ``"supportedSubmitMethods":[]`` disables the sandbox

.. code-block:: bash

    $ php bin/console nelmio:apidoc:dump --format=html --html-config '{"assets_mode":"offline","server_url":"https://example.com","swagger_ui_config":{"supportedSubmitMethods":[]}}' > api.html

.. _`configure Swagger UI`: https://swagger.io/docs/open-source-tools/swagger-ui/usage/configuration/
