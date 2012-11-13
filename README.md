NelmioApiDocBundle
==================

[![Build Status](https://secure.travis-ci.org/nelmio/NelmioApiDocBundle.png?branch=master)](http://travis-ci.org/nelmio/NelmioApiDocBundle)

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation for your APIs.

**Important:** This bundle is developed in sync with [symfony's repository](https://github.com/symfony/symfony).
For Symfony 2.0.x, you need to use the 1.* version of the bundle.


## Installation ##

Add this bundle to your `composer.json` file:

    {
        "require": {
            "nelmio/api-doc-bundle": "dev-master"
        }
    }

Register the bundle in `app/AppKernel.php`:

    // app/AppKernel.php
    public function registerBundles()
    {
        return array(
            // ...
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        );
    }

Import the routing definition in `routing.yml`:

    # app/config/routing.yml
    NelmioApiDocBundle:
        resource: "@NelmioApiDocBundle/Resources/config/routing.yml"
        prefix:   /api/doc

Enable the bundle's configuration in `app/config/config.yml`:

    # app/config/config.yml
    nelmio_api_doc: ~


## Usage ##

The main problem with documentation is to keep it up to date. That's why the **NelmioApiDocBundle**
uses introspection a lot. Thanks to an annotation, it's really easy to document an API method.

### The ApiDoc() annotation ###

The bundle provides an `ApiDoc()` annotation for your controllers:

``` php
<?php

namespace Your\Namespace;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class YourController extends Controller
{
    /**
     * This the documentation description of your method, it will appear
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
     *  return="Your\Namespace\Class"
     * )
     */
    public function postAction()
    {
    }
}
```

The following properties are available:

* `resource`: whether the method describes a main resource or not (default: `false`);

* `description`: a description of the API method;

* `filters`: an array of filters;

* `input`: the input type associated to the method, currently this supports Form Types, and classes with JMS Serializer
 metadata, useful for POST|PUT methods, either as FQCN or as form type (if it is registered in the form factory in the container)

* `return`: the return type associated with the response.  Specified and parsed the same way as `input`.

* `statusCodes`: an array of HTTP status codes and a description of when that status is returned; Example:

``` php
<?php

class YourController
{
    /**
     * @ApiDoc(
     *     statusCodes={
     *         200="Returned when successful",
     *         403="Returned when the user is not authorized to say hello"},
     * )
     */
    public function myFunction()
    {
        // ...
    }
}
```

Each _filter_ has to define a `name` parameter, but other parameters are free. Filters are often optional
parameters, and you can document them as you want, but keep in mind to be consistent for the whole documentation.

If you set `input`, then the bundle automatically extracts parameters based on the given type,
and determines for each parameter its data type, and if it's required or not.

For Form Types, you can add an extra option named `description` on each field:

For classes parsed with JMS metadata, description will be taken from the properties doc comment, if available.

``` php
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

The bundle will also get information from the routing definition (`requirements`, `pattern`, etc), so to get the
best out of it you should define strict _method requirements etc.


### Documentation on-the-fly ###

By calling an URL with the parameter `?_doc=1`, you will get the corresponding documentation if available.


### Web Interface ###

You can browse the whole documentation at: `http://example.org/api/doc`.

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview.png)

![](https://github.com/nelmio/NelmioApiDocBundle/raw/master/Resources/doc/webview2.png)


### Command ###

A command is provided in order to dump the documentation in `json`, `markdown`, or `html`.

    php app/console api:doc:dump [--format="..."]

The `--format` option allows to choose the format (default is: `markdown`).

For example to generate a static version of your documentation you can use:

    php app/console api:doc:dump --format=html > api.html

By default, the generated HTML will add the sandbox feature if you didn't disable it in the configuration.
If you want to generate a static version of your documentation without sandbox, use the `--no-sandbox` option.


## Configuration ##

You can specify your own API name:

    # app/config/config.yml
    nelmio_api_doc:
        name:   My API

This bundle provides a sandbox mode in order to test API methods. You can
configure this sandbox using the following parameters:

    # app/config/config.yml
    nelmio_api_doc:
        sandbox:
            authentication: # default null, if set, the value of the api key is read from the query string and appended to every sandbox api call
                name: access_token
                delivery: query # only query delivery is supported for now
            enabled:  true # default: true, you can set this parameter to `false` to disable the sandbox
            endpoint: http://sandbox.example.com/ # default: /app_dev.php, use this parameter to define which URL to call through the sandbox


The bundle provides a way to register multiple `input` parsers.  The first parser that can handle the specified
input is used, so you can configure their priorities via container tags.  Here's an example parser service registration:

    #app/config/config.yml
    services:
        mybundle.api_doc.extractor.custom_parser:
            class: MyBundle\Parser\CustomDocParser;
            tags:
                - {name: nelmio_api_doc.extractor.parser, priority: 2}

## Credits ##

The design is heavily inspired by the [swagger-ui](https://github.com/wordnik/swagger-ui) project.
