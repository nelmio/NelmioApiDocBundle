# CHANGELOG

* Add filtering model properties by route area to `JMSModelDescriber`

## 4.33.5
* Added new optional parameter `$context` to` PropertyDescriberInterface::supports()`

## 4.33.4
* Deprecated `null` type from `$options` in `Nelmio\ApiDocBundle\Attribute\Model::__construct()`. Pass an empty array (`[]`) instead.
* Deprecated `null` type from `$options` in `NNelmio\ApiDocBundle\Attribute\Model::__construct()`. Pass an empty array (`[]`) instead.

## 4.33.3
* Bumped swagger-ui files from `5.18.1` to `5.18.2`
* Bumped redoc files to `2.2.0`

## 4.33.2
* Fixed incorrect directory updated for swagger-ui files from version `4.33.2`

## 4.33.1
* Bumped swagger-ui files to `5.18.1`
* Fixed explicitly set default values defined in `#[OA\Property]` being overwritten

## 4.33.0
* Fixed custom JMS enum type handling
* Added support for name based serialisation of JMS enums

## 4.32.3

* Deprecated `Nelmio\ApiDocBundle\Annotation` namespace in favor of `Nelmio\ApiDocBundle\Attribute` namespace in preparation for 5.x. Consider upgrading to the new attribute syntax.
```diff
- use Nelmio\ApiDocBundle\Annotation\Areas;
- use Nelmio\ApiDocBundle\Annotation\Model;
- use Nelmio\ApiDocBundle\Annotation\Operation;
- use Nelmio\ApiDocBundle\Annotation\Security;

+ use Nelmio\ApiDocBundle\Attribute\Areas;
+ use Nelmio\ApiDocBundle\Attribute\Model;
+ use Nelmio\ApiDocBundle\Attribute\Operation;
+ use Nelmio\ApiDocBundle\Attribute\Security;
```


## 4.32.0

* Added support to configure `options` and `serializationContext` via `nelmio_api_doc.models.names`.
* Fixed `serializationContext` not being applied to nested models.

## 4.31.0

* Added support to opt out of JMS serializer usage per endpoint by setting `useJms` in the serializationContext.
  ```php
  #[OA\Response(response: 200, content: new Model(type: UserDto::class, serializationContext: ["useJms" => false]))]
  ```

## 4.30.0
* Create top level OpenApi Tag from Tags top level annotations/attributes

## 4.25.3

* Calling `DocumentationExtension::getExtendedType()` has been deprecated in favor of `DocumentationExtension::getExtendedTypes()` to align with the deprecation introduced with `symfony/symfony` version `4.2`.


## 4.26.0

* Add ability to configure UI through configuration
```yaml
nelmio_api_doc:
  html_config:
    assets_mode: bundle
    redocly_config:
      expandResponses: '200,201'
      hideDownloadButton: true
    swagger_ui_config:
      deepLinking: true
```

## 4.25.0

* Added support for [JMS @Discriminator](https://jmsyst.com/libs/serializer/master/reference/annotations#discriminator) annotation/attribute
  ```php
  #[\JMS\Serializer\Annotation\Discriminator(field: 'type', map: ['car' => Car::class, 'plane' => Plane::class])]
  abstract class Vehicle { }
  class Car extends Vehicle { }
  class Plane extends Vehicle { }
  ```

## 4.24.0

* Added support for some integer ranges (https://phpstan.org/writing-php-code/phpdoc-types#integer-ranges).
  Annotations attached to integer properties like:
  ```php
    /**
     * @var int<6, 11>
     * @var int<min, 11>
     * @var int<6, max>
     * @var positive-int
     * @var negative-int
     */
  ```
  will be interpreted as appropriate `minimum` and `maximum` properties in the generated OpenAPI specification.

### Minor breaking change
Dropped support for PHP 7.2 and PHP 7.3. PHP 7.4 is the minimum required version now.

## 4.23.0

* Cache configuration option `nelmio_api_doc.cache.item_id` now automatically gets the area appended.
  ```yml
  nelmio_api_doc:
      cache:
          pool: app.cache
          item_id: nelmio_api_doc.docs
      areas:
          default:
              ...
          area1:
              ...
  ```
  Result in cache keys: `nelmio_api_doc.docs.default` & `nelmio_api_doc.docs.area1` to be used respectively.
* Added cache configuration option per area.
  ```yml
  nelmio_api_doc:
      areas:
          default: # Manual cache configuration
              cache:
                  pool: app.cache
                  item_id: nelmio_api_doc.docs.default
              ...
          area1:
              cache:
                  pool: app.cache
                  item_id: nelmio_api_doc.docs.area1
              ...
  ```
  Non-configured options will be inherited from `nelmio_api_doc.cache`.
* Fixed vendor extensions (`x-*`) from configuration not being outputted in the generated specification.
  ```yml
  nelmio_api_doc:
      documentation:
          info:
              title: 'My API'
              description: 'My API description'
              x-foo: 'bar'
  ```
  Now results in JSON specification:
  ```json
  {
    ...
    "info": {
      "title": "API",
      "version": "1.0",
      "x-foo": "bar"
    },
    ...
  }
  ```
* Updated nullable enum handling to align with the behaviour of other object types. It now uses wraps nullable enums with `oneOf` instead of `allOf`.

## 4.22.0

* Updated bundle directory structure to recommended file structure as described in https://symfony.com/doc/7.0/bundles/best_practices.html.

  It might be necessary to reinstall the assets:
  ```bash
    bin/console assets:install
  ```

### Breaking change
If your codebase mentions a file or directory by path then an update to this path is necessary. For example to following configuration:
```yaml
doc-api:
    resource: "@NelmioApiDocBundle/Resources/config/routing/swaggerui.xml"
    prefix: /api/doc
```
Becomes:
```yaml
doc-api:
    resource: "@NelmioApiDocBundle/config/routing/swaggerui.xml"
    prefix: /api/doc
```

## 4.21.0

* Added bundle configuration options `nelmio_api_doc.cache.pool` and `nelmio_api_doc.cache.item_id`.
  ```yml
  nelmio_api_doc:
      cache:
          pool: app.cache
          item_id: nelmio_api_doc.docs
  ```

## 4.20.0

* Added Redocly as an alternative to Swagger UI. https://github.com/Redocly/redoc.
* Added support for describing dictionary types in OpenAPI 3.0.

## 4.17.0

* Passing groups to `PropertyDescriberInterface::describe()` via the `$groups` parameter is deprecated, the parameter will get removed in a future version. Pass groups via `$context['groups']` instead.


## 4.0.0

* Added support of OpenAPI 3.0. The internals were completely reworked and this version introduces BC breaks.

## 3.7.0


* Added `@SerializedName` annotation support and name converters when using Symfony >= 4.2.
* Removed pattern added from the Expression Violation message.
* Added FOSRestBundle 3.x support
* Added `@SWG` annotations support at methods level in models

## 3.3.0


* Usage of Google Fonts was removed. System fonts `serif` / `sans` will be used instead.
  This can lead to a different look on different operating systems.
  You can [re-add Google Fonts again manually by overriding the template](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/faq.html#re-add-google-fonts).

* The Twig template for the Swagger UI now contains blocks to make it easier to overwrite certain parts.
  See the [official documentation](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/customization.html) how to do this.

## 3.2.0 (2018-03-24)

* Add a documentation form extension. Use the ``documentation`` option to define how a form field is documented.
* Allow references to config definitions in controllers.
* Using `@Model` implicitly in `@SWG\Schema`, `@SWG\Items` and `@SWG\Property` is deprecated. Use `ref=@Model()` instead.

  Before:
  ```php
  /**
   * This was considered as an array of models.
   *
   * @SWG\Property(@Model(type=FooClass::class))
   */
  ```

  After:
  ```php
  /**
   * For an individual object:
   * @SWG\Property(ref=@Model(type=FooClass::class))
   *
   * For an array:
   * @SWG\Property(type="array", @SWG\Items(ref=@Model(type=FooClass::class)))
   */
  ```

Config
* `nelmio_api_doc.areas` added support to filter by host patterns.

  ```yml
  nelmio_api_doc:
      areas: [ host_patterns: [ ^api\. ] ]
  ```

* Added dependency for "symfony/options-resolver:^3.4.4|^4.0"

## 3.1.0 (2018-01-28)

* Added Symfony Validator constraints support

Symfony Forms
* Support for boolean checkbox
* Support for integer

JMS Serializer
* Support JMS `int` (alias for `integer`)
* Also process phpdoc annotations

SwaggerPHP
* Handle `enum` and `default` properties from SwaggerPHP annotation
* Support `@Security` annotations

Config
* `nelmio_api_doc.routes` has been replaced by `nelmio_api_doc.areas`. Please update your config accordingly.

  Before:
  ```yml
  nelmio_api_doc:
      routes: [ path_patterns: [ /api ] ]
  ```

  After:
  ```yml
  nelmio_api_doc:
      areas: [ path_patterns: [ /api ] ]
  ```

## 3.0.0 (2017-12-10)

Large refactoring introducing `zircote/swagger-php` for swagger annotations.

See UPGRADE-3.0.md for upgrading instructions.
