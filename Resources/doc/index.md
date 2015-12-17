NelmioApiDocBundle
==================

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation
for your APIs.

* [Installation](#installation)
* [Usage](#usage)
  - [The `ApiDoc()` Annotation](the-apidoc-annotation.md)
  - [Multiple API Documentation a.k.a. "Views"](multiple-api-doc.md)
  - [Other Bundle Annotations](other-bundle-annotations.md)
  - [Swagger Support](swagger-support.md)
  - [DunglasApiBundle Support](dunglasapibundle.md)
  - [Sandbox](sandbox.md)
  - [Commands](commands.md)
* [Configuration In-Depth](configuration-in-depth.md)
* [Frequently Asked Questions](faq.md)
* [Configuration Reference](configuration-reference.md)


Installation
------------

Require the `nelmio/api-doc-bundle` package in your composer.json and update
your dependencies.

    $ composer require nelmio/api-doc-bundle

Register the bundle in `app/AppKernel.php`:

```php
// app/AppKernel.php
public function registerBundles()
{
    return array(
        // ...
        new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
    );
}
```

Import the routing definition in `routing.yml`:

```yaml
# app/config/routing.yml
NelmioApiDocBundle:
    resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
    prefix:   /api/doc
```

Enable the bundle's configuration in `app/config/config.yml`:

```yaml
# app/config/config.yml
nelmio_api_doc: ~
```

The **NelmioApiDocBundle** requires Twig as a template engine so do not forget
to enable it:

```yaml
# app/config/config.yml
framework:
    templating:
        engines: ['twig']
```

Usage
-----

The main problem with documentation is to keep it up to date. That's why the
**NelmioApiDocBundle** uses introspection a lot. Thanks to an annotation, it's
really easy to document an API method. The following chapters will help you
setup your API documentation:

* [The `ApiDoc()` Annotation](the-apidoc-annotation.md)
* [Multiple API Documentation a.k.a. "Views"](multiple-api-doc.md)
* [Other Bundle Annotations](other-bundle-annotations.md)
* [Swagger Support](swagger-support.md)
* [DunglasApiBundle Support](dunglasapibundle.md)
* [Sandbox](sandbox.md)
* [Commands](commands.md)

### Web Interface

You can browse the whole documentation at: `http://example.org/api/doc`.

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview.png)

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview2.png)

### On-The-Fly Documentation

By calling an URL with the parameter `?_doc=1`, you will get the corresponding
documentation if available.
=======

### Sandbox

This bundle provides a sandbox mode in order to test API methods. You can
configure this sandbox using the following parameters:
```yaml
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
```
### Command

A command is provided in order to dump the documentation in `json`, `markdown`, or `html`.

    php app/console api:doc:dump [--format="..."]

The `--format` option allows to choose the format (default is: `markdown`).

For example to generate a static version of your documentation you can use:

    php app/console api:doc:dump --format=html > api.html

By default, the generated HTML will add the sandbox feature if you didn't disable it in the configuration.
If you want to generate a static version of your documentation without sandbox, use the `--no-sandbox` option.

### Swagger support

Read the [documentation for Swagger integration](https://github.com/nelmio/NelmioApiDocBundle/blob/master/Resources/doc/swagger-support.md) for the necessary steps to make a Swagger-compliant documentation for your API.

### Caching

It is a good idea to enable the internal caching mechanism on production:

    # app/config/config.yml
    nelmio_api_doc:
        cache:
            enabled: true

Configuration In-Depth
----------------------

You can specify your own API name:
```
# app/config/config.yml
nelmio_api_doc:
    name: My API
```
You can choose between different authentication methods:
```yaml
# app/config/config.yml
nelmio_api_doc:
    authentication:
        delivery: header
        name:     X-Custom

# app/config/config.yml
nelmio_api_doc:
    authentication:
        delivery: query
        name:     param

# app/config/config.yml
nelmio_api_doc:
    authentication:
        delivery: http
        type:     basic # or bearer
```
When choosing an `http` delivery, `name` defaults to `Authorization`,
and the header value will automatically be prefixed by the corresponding type (ie. `Basic` or `Bearer`).

You can specify which sections or patterns to exclude from the documentation generation:
```yaml
# app/config/config.yml
nelmio_api_doc:
    exclude_sections: ["privateapi", "testapi"]
    exclude_patterns: ["/en", "/notused/route"]
```
The bundle provides a way to register multiple `input` parsers. The first parser
that can handle the specified input is used, so you can configure their
priorities via container tags. Here's an example parser service registration:
```yaml
# app/config/config.yml
services:
    mybundle.api_doc.extractor.custom_parser:
        class: MyBundle\Parser\CustomDocParser
        tags:
            - { name: nelmio_api_doc.extractor.parser, priority: 2 }
```
You can also define your own motd content (above methods list). All you have to
do is add to configuration:
```yaml
# app/config/config.yml
nelmio_api_doc:
    # ...
    motd:
        template: AcmeApiBundle::Components/motd.html.twig
```         
You can define an alternate location where the ApiDoc configurations are to be cached:
```yaml    
# app/config/config.yml
nelmio_api_doc:
    cache:
        enabled: true
        file: "/tmp/symfony-app/%kernel.environment%/api-doc.cache"
```
### Using Your Own Annotations

If you have developed your own project-related annotations, and you want to parse them to populate
the `ApiDoc`, you can provide custom handlers as services. You just have to implement the
`Nelmio\ApiDocBundle\Extractor\HandlerInterface` and tag it as `nelmio_api_doc.extractor.handler`:

```yaml
# app/config/config.yml
services:
    mybundle.api_doc.extractor.my_annotation_handler:
        class: MyBundle\AnnotationHandler\MyAnnotationHandler
        tags:
            - { name: nelmio_api_doc.extractor.handler }
```
Look at the built-in [Handlers](https://github.com/nelmio/NelmioApiDocBundle/tree/master/Extractor/Handler).

### Reference Configuration

``` yaml
nelmio_api_doc:
    name:                 'API documentation'
    exclude_sections:     []
    exclude_patterns:     []
    default_sections_opened:  true
    motd:
        template:             'NelmioApiDocBundle::Components/motd.html.twig'
    request_listener:
        enabled:              true
        parameter:            _doc
    sandbox:
        enabled:              true
        endpoint:             null
        accept_type:          null
        body_format:
            formats:

                # Defaults:
                - form
                - json
            default_format:       ~ # One of "form"; "json"
        request_format:
            formats:

                # Defaults:
                json:                application/json
                xml:                 application/xml
            method:               ~ # One of "format_param"; "accept_header"
            default_format:       json
        authentication:
            delivery:             ~ # Required
            name:                 ~ # Required

            # Required if http delivery is selected.
            type:                 ~ # One of "basic"; "bearer"
            custom_endpoint:      false
    swagger:
        api_base_path:        /api
        swagger_version:      '1.2'
        api_version:          '0.1'
        info:
            title:                Symfony2
            description:          'My awesome Symfony2 app!'
            TermsOfServiceUrl:    null
            contact:              null
            license:              null
            licenseUrl:           null
    cache:
        enabled:              false
        file:                 '%kernel.cache_dir%/api-doc.cache'
```