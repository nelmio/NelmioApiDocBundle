# ApiDocBundle

[WIP] Generates Swagger docs from several sources

## Installation

Just like any bundle, you have to download it using composer:
```
composer require nelmio/api-doc-bundle dev-master
```

And then add it to your kernel:
```php
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...

            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
        ];

        // ...
    }
}
```

And that's all, no configuration needed!

## What does this bundle?

It generates you a swagger documentation from your symfony app thanks to
different sources called _Describers_. These _Describers_ are specific
to a library and extract data from it and merge it into your swagger
documentation.

You can fetch your swagger documentation in your app:
```php
$generator = $container->get('nelmio_api_doc.generator');
$swagger = $generator->generate()->toArray();
```

## What's supported?

This bundle supports _Symfony_ route requirements, PHP annotations,
[_Swagger-Php_](https://github.com/zircote/swagger-php) annotations,
[_NelmioApiDocBundle_](https://github.com/nelmio/NelmioApiDocBundle) annotation,
[_FOSRestBundle_](https://github.com/FriendsOfSymfony/FOSRestBundle) annotations and
[_Api-Platform_](https://github.com/api-platform/api-platform) apps.

This bundle is a **Work In Progress** and as such it does only support input
documentation for now (if you use _Swagger-Php_ or _Api-Platform_ output is supported as well).

## What's next?

The hardest part remain: **models**. We have to build something to
manage models that can vary based on several factors (serialization
groups, class, etc.) and then put it in the app's documentation.

Other libraries support might be added but the priority is to finalize the bundle first.
