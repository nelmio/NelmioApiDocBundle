Frequently Asked Questions
==========================

How can I remove the parameter ``_format`` sent in ``POST`` and ``PUT`` request?
--------------------------------------------------------------------------------

.. code-block:: yaml

    nelmio_api_doc:
        sandbox:
            request_format:
                method: accept_header
