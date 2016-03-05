Sandbox
=======

This bundle provides a sandbox mode in order to test API methods. You can
configure this sandbox using the following parameters:

.. code-block:: yaml

    # app/config/config.yml
    nelmio_api_doc:
        sandbox:
            authentication:             # default is `~` (`null`), if set, the sandbox automatically
                                        # send authenticated requests using the configured `delivery`

                name: access_token      # access token name or query parameter name or header name

                delivery: http          # `query`, `http`, and `header` are supported

                # Required if http delivery is selected.
                type:     basic         # `basic`, `bearer` are supported

                custom_endpoint: true   # default is `false`, if `true`, your user will be able to
                                        # specify its own endpoint

            enabled:  true              # default is `true`, you can set this parameter to `false`
                                        # to disable the sandbox

            endpoint: http://sandbox.example.com/   # default is `/app_dev.php`, use this parameter
                                                    # to define which URL to call through the sandbox

            accept_type: application/json           # default is `~` (`null`), if set, the value is
                                                    # automatically populated as the `Accept` header

            body_format:
                formats: [ form, json ]             # array of enabled body formats,
                                                    # remove all elements to disable the selectbox
                default_format: form                # default is `form`, determines whether to send
                                                    # `x-www-form-urlencoded` data or json-encoded
                                                    # data (by setting this parameter to `json`) in
                                                    # sandbox requests

            request_format:
                formats:                            # default is `json` and `xml`,
                    json: application/json          # override to add custom formats or disable
                    xml: application/xml            # the default formats

                method: format_param    # default is `format_param`, alternately `accept_header`,
                                        # decides how to request the response format

                default_format: json    # default is `json`,
                                        # default content format to request (see formats)

            entity_to_choice: false     # default is `true`, if `false`, entity collection
                                        # will not be mapped as choice
