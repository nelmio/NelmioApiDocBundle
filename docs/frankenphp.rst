FrankenPHP and long-running workers
===================================

NelmioApiDocBundle works with `FrankenPHP`_ worker mode and other long-running
PHP runtimes that reuse the Symfony kernel across requests (for example
RoadRunner or Swoole with Symfony's kernel reset).

In these environments, the generated OpenAPI document is kept in memory after
the first successful generation so later requests can reuse it without rebuilding
the full documentation.

Recommended cache configuration
-------------------------------

Configure a PSR-6 cache pool so documentation survives worker restarts and can
be shared across workers:

.. code-block:: yaml

    # config/packages/nelmio_api_doc.yaml
    nelmio_api_doc:
        cache:
            pool: cache.system

Forcing regeneration on every request
-------------------------------------

By default the in-memory document is kept between requests. If you need to
regenerate documentation on every request, tag the generator with
``kernel.reset``:

.. code-block:: yaml

    # config/services.yaml
    services:
        nelmio_api_doc.generator.default:
            tags:
                - { name: kernel.reset, method: reset }

.. _`FrankenPHP`: https://frankenphp.dev/
