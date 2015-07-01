NelmioApiDocBundle
==================

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation
for your APIs.


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
really easy to document an API method.

### The ApiDoc() Annotation

The bundle provides an `ApiDoc()` annotation for your controllers:

```php
<?php

namespace Your\Namespace;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class YourController extends Controller
{
    /**
     * This is the documentation description of your method, it will appear
     * on a specific pane. It will read all the text until the first
     * annotation.
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of your API method",
     *  filters={
     *      {"name"="a-filter", "dataType"="integer"},
     *      {"name"="another-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}
     *  }
     * )
     */
    public function getAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Create a new Object",
     *  input="Your\Namespace\Form\Type\YourType",
     *  output="Your\Namespace\Class"
     * )
     */
    public function postAction()
    {
    }

    /**
     * @ApiDoc(
     *  description="Returns a collection of Object",
     *  requirements={
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="how many objects to return"
     *      }
     *  },
     *  parameters={
     *      {"name"="categoryId", "dataType"="integer", "required"=true, "description"="category id"}
     *  }
     * )
     */
    public function cgetAction($id)
    {
    }
}
```

The following properties are available:

* `section`: allow to group resources

* `resource`: whether the method describes a main resource or not (default: `false`);

* `description`: a description of the API method;

* `https`: whether the method described requires the https protocol (default: `false`);

* `deprecated`: allow to set method as deprecated (default: `false`);

* `tags`: allow to tag a method (e.g. `beta` or `in-development`). Either a single tag or an array of tags. Each tag can have an optional hex colorcode attached.

```php
<?php

class YourController
{
    /**
     * @ApiDoc(
     *     tags={
     *         "stable",
     *         "deprecated" = "#ff0000"
     *     }
     * )
     */
    public function myFunction()
    {
        // ...
    }
}
```

* `filters`: an array of filters;

* `requirements`: an array of requirements;

* `parameters`: an array of parameters;

* `input`: the input type associated to the method (currently this supports Form Types, classes with JMS Serializer
 metadata, classes with Validation component metadata and classes that implement JsonSerializable) useful for POST|PUT methods, either as FQCN or as form type
 (if it is registered in the form factory in the container).

* `output`: the output type associated with the response.  Specified and parsed the same way as `input`.

* `statusCodes`: an array of HTTP status codes and a description of when that status is returned; Example:

```php
<?php

class YourController
{
    /**
     * @ApiDoc(
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello",
     *         404={
     *           "Returned when the user is not found",
     *           "Returned when something else is not found"
     *         }
     *     }
     * )
     */
    public function myFunction()
    {
        // ...
    }
}
```

* `views`: the view(s) under which this resource will be shown. Leave empty to
  specify the default view. Either a single view, or an array of views.

Each _filter_ has to define a `name` parameter, but other parameters are free. Filters are often optional
parameters, and you can document them as you want, but keep in mind to be consistent for the whole documentation.

If you set `input`, then the bundle automatically extracts parameters based on the given type,
and determines for each parameter its data type, and if it's required or not.

For classes parsed with JMS metadata, description will be taken from the properties doc comment, if available.

For Form Types, you can add an extra option named `description` on each field:

```php
<?php

class YourType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilder $builder, array $options)
    {
        $builder->add('note', null, array(
            'description' => 'this is a note',
        ));

        // ...
    }
}
```

The bundle will also get information from the routing definition
(`requirements`, `pattern`, etc), so to get the best out of it you should
define strict _method requirements etc.

### Multiple API Documentation ("Views")

With the `views` tag in the `@ApiDoc` annotation, it is possible to create
different views of your API documentation. Without the tag, all methods are
located in the `default` view, and can be found under the normal API
documentation url.

You can specify one or more _view_ names under which the method will be
visible.

An example:

```php
    /**
     * A resource
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of your API method",
     *  views = { "default", "premium" }
     * )
     */
    public function getAction()
    {
    }

    /**
     * Another resource
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  views = { "premium" }
     * )
     */
    public function getAnotherAction()
    {
    }
```

In this case, only the first resource will be available under the default view,
while both methods will be available under the `premium` view.

#### Accessing Specific API Views

The `default` view can be found at the normal location. Other views can be
found at `http://your.documentation/<view name>`.

For instance, if your documentation is located at:

        http://example.org/doc/api/v1/

then the `premium` view will be located at:

        http://example.org/doc/api/v1/premium


### Other Bundle Annotations

Also bundle will get information from the other annotations:

* `@FOS\RestBundle\Controller\Annotations\RequestParam` - use as `parameters`

* `@FOS\RestBundle\Controller\Annotations\QueryParam` - use as `requirements` (when strict parameter is true), `filters` (when strict is false)

* `@JMS\SecurityExtraBundle\Annotation\Secure` - set `authentication` to true, `authenticationRoles` to the given roles

* `@Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache` - set `cache`

### PHPDoc

Actions marked as `@deprecated` will be marked as such in the interface.

#### JMS Serializer Features

The bundle has support for some of the JMS Serializer features and uses this
extra piece of information in the generated documentation.

##### Group Exclusion Strategy

If your classes use [JMS Group Exclusion
Strategy](http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#creating-different-views-of-your-objects),
you can specify which groups to use when generating the documentation by using
this syntax :

 ```php
 input={
     "class"="Acme\Bundle\Entity\User",
     "groups"={"update", "public"}
 }
 ```

In this case the groups 'update' and 'public' are used.

This feature also works for the `output` property.

##### Versioning Objects

If your `output` classes use [versioning capabilities of JMS
Serializer](http://jmsyst.com/libs/serializer/master/cookbook/exclusion_strategies#versioning-objects),
the versioning information will be automatically used when generating the
documentation.

#### Form Types Features

Even if you use `FormFactoryInterface::createNamed('', 'your_form_type')` the documentation will generate the form type name as the prefix for inputs
(`your_form_type[param]` ... instead of just `param`).

You can specify which prefix to use with the `name` key in the `input` section:

```php
input = {
 "class" = "your_form_type",
 "name" = ""
}
```

You can also add some options to pass to the form. You just have to use the `options` key:

```php
input = {
 "class" = "your_form_type",
 "options" = {"method" = "PUT"},
}
```

#### Used Parsers

By default, all registered parsers are used, but sometimes you may want to
define which parsers you want to use. The `parsers` attribute is used to
configure a list of parsers that will be used:

```php
output={
    "class"   = "Acme\Bundle\Entity\User",
    "parsers" = {
        "Nelmio\ApiDocBundle\Parser\JmsMetadataParser",
        "Nelmio\ApiDocBundle\Parser\ValidationParser"
    }
}
```

In this case the parsers `JmsMetadataParser` and `ValidationParser` are used to
generate returned data.

This feature also works for both the `input` and `output` properties.

### Web Interface

You can browse the whole documentation at: `http://example.org/api/doc`.

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview.png)

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview2.png)

### On-The-Fly Documentation

By calling an URL with the parameter `?_doc=1`, you will get the corresponding
documentation if available.

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

        entity_to_choice: false     # default is `true`, if `false`, entity collection
                                    # will not be mapped as choice
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

Read the [documentation for Swagger integration](https://github.com/nelmio/NelmioApiDocBundle/blob/master/Resources/doc/swagger-support.md)
for the necessary steps to make a Swagger-compliant documentation for your API.

### DunglasApiBundle support

This bundle recognizes and documents resources exposed with [DunglasApiBundle](https://github.com/dunglas/DunglasApiBundle).
Just install NelmioApiDoc and the documentation will be automatically available. To enable the sandbox, use the following
configuration:

```yaml
# app/config/config.yml
nelmio_api_doc:
    sandbox:
        accept_type:        "application/json"
        body_format:
            formats:        [ "json" ]
            default_format: "json"
        request_format:
            formats:
                json:       "application/json"
```

### Caching

It is a good idea to enable the internal caching mechanism on production:

```yaml
# app/config/config.yml
nelmio_api_doc:
    cache:
        enabled: true
```

Configuration In-Depth
----------------------

You can specify your own API name:

```yaml
# app/config/config.yml
nelmio_api_doc:
    name: My API
```
You can choose between different authentication methods:

```yaml
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
```
When choosing an `http` delivery, `name` defaults to `Authorization`,
and the header value will automatically be prefixed by the corresponding type (ie. `Basic` or `Bearer`).

You can specify which sections to exclude from the documentation generation:

```yaml
# app/config/config.yml
nelmio_api_doc:
    exclude_sections: ["privateapi", "testapi"]
```

Note that `exclude_sections` will literally exclude a section from your api
documentation. It's possible however to create multiple views by specifying the
`views` parameter within the `@ApiDoc` annotations. This allows you to move
private or test methods to a complete different view of your documentation
instead.

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

### Configuration Reference

``` yaml
nelmio_api_doc:
    name:                 'API documentation'
    exclude_sections:     []
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
        entity_to_choice:         true
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
