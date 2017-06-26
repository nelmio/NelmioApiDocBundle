NelmioApiDocBundle
==================

[![Build
Status](https://secure.travis-ci.org/nelmio/NelmioApiDocBundle.png?branch=master)](http://travis-ci.org/nelmio/NelmioApiDocBundle)
[![Total Downloads](https://poser.pugx.org/nelmio/api-doc-bundle/downloads)](https://packagist.org/packages/nelmio/api-doc-bundle)
[![Latest Stable
Version](https://poser.pugx.org/nelmio/api-doc-bundle/v/stable)](https://packagist.org/packages/nelmio/api-doc-bundle)

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation
for your APIs.

## Migrate from 2.x to 3.0

[To migrate from 2.x to 3.0, just follow our guide.](https://github.com/nelmio/NelmioApiDocBundle/blob/master/UPGRADE-3.0.md)

## Installation

First, open a command console, enter your project directory and execute the following command to download the latest version of this bundle (still in beta, for a stable version look [here](https://github.com/nelmio/NelmioApiDocBundle/tree/2.x)):

```
composer require nelmio/api-doc-bundle dev-master
```

Then add the bundle to your kernel:
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

To browse your documentation with Swagger UI, register the following route:

```yml
# app/config/routing.yml
app.swagger_ui:
    resource: "@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml"
    prefix:   /api/doc
```

If you also want to expose it in JSON, register this route:

```yml
# app/config/routing.yml
app.swagger:
    path: /api/doc.json
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger }
```

## What does this bundle?

It generates you a swagger documentation from your symfony app thanks to
_Describers_. Each of these _Describers_ extract infos from various sources.
For instance, one extract data from SwaggerPHP annotations, one from your
routes, etc.

If you configured the ``app.swagger_ui`` route above, you can browse your
documentation at `http://example.org/api/doc`.

## Configure the bundle

As you just installed the bundle, you'll likely see routes you don't want in
your documentation such as `/_profiler/`. To fix this, you can filter the
routes that are documented by configuring the bundle:

```yml
# app/config/config.yml
nelmio_api_doc:
    routes:
        path_patterns: # an array of regexps
            - ^/api
```

## Use the bundle

You can configure globally your documentation in the config (take a look at
[the Swagger specification](http://swagger.io/specification/) to know the fields
available):

```yml
nelmio_api_doc:
    documentation:
        info:
            title: My App
            description: This is an awesome app!
            version: 1.0.0
```

To document your routes, you can use annotations in your controllers:

```php
namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\Reward;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    /*
     * @Route("/api/{user}/rewards", methods={"GET"})
     * @SWG\Response(
     *     response=200,
     *     description="Returns the rewards of an user",
     *     @SWG\Schema(
     *         type="array",
     *         @Model(type=Reward::class, groups={"full"})
     *     )
     * )
     * @SWG\Parameter(
     *     name="order",
     *     in="query",
     *     type="string",
     *     description="The field used to order rewards"
     * )
     * @SWG\Tag(name="rewards")
     */
    public function fetchUserRewardsAction(User $user)
    {
        // ...
    }
}
```

## What's supported?

This bundle supports _Symfony_ route requirements, PHP annotations,
[_Swagger-Php_](https://github.com/zircote/swagger-php) annotations,
[_FOSRestBundle_](https://github.com/FriendsOfSymfony/FOSRestBundle) annotations
and apps using [_Api-Platform_](https://github.com/api-platform/api-platform).

It supports models through the ``@Model`` annotation.

## Contributing

See
[CONTRIBUTING](https://github.com/nelmio/NelmioApiDocBundle/blob/master/CONTRIBUTING.md)
file.

## Running the Tests

Install the [Composer](http://getcomposer.org/) dependencies:

    git clone https://github.com/nelmio/NelmioApiDocBundle.git
    cd NelmioApiDocBundle
    composer update

Then run the test suite:

    ./phpunit

## License

This bundle is released under the MIT license.
