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
