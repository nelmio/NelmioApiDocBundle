CHANGELOG
=========

3.3.0 (unreleased)
------------------

* Usage of Google Fonts was removed. System fonts `serif` / `sans` will be used instead. 
  This can lead to a different look on different operating systems.
  You can add the Google Fonts again manually by overriding the template:
  
  * Create a new file ``
  * Add the following content:
    ```twig
    {% block stylesheets %}
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Open+Sans:400,700|Source+Code+Pro:300,600|Titillium+Web:400,600,700">
        {{ parent() }}
        <style type="text/css" rel="stylesheet">
            #formats {
                font-family: Open Sans,sans-serif;
            }
    
            .swagger-ui .opblock-tag,
            .swagger-ui .opblock .opblock-section-header label,
            .swagger-ui .opblock .opblock-section-header h4,
            .swagger-ui .opblock .opblock-summary-method,
            .swagger-ui .tab li,
            .swagger-ui .scheme-container .schemes>label,
            .swagger-ui .loading-container .loading:after,
            .swagger-ui .btn,
            .swagger-ui .btn.cancel,
            .swagger-ui select,
            .swagger-ui label,
            .swagger-ui .dialog-ux .modal-ux-content h4,
            .swagger-ui .dialog-ux .modal-ux-header h3,
            .swagger-ui section.models h4,
            .swagger-ui section.models h5,
            .swagger-ui .model-title,
            .swagger-ui .parameter__name,
            .swagger-ui .topbar a,
            .swagger-ui .topbar .download-url-wrapper .download-url-button,
            .swagger-ui .info .title small pre,
            .swagger-ui .scopes h2,
            .swagger-ui .errors-wrapper hgroup h4 {
                font-family: Open Sans,sans-serif!important;
            }
        </style>
    {% endblock stylesheets %}
    ```

* The Twig template for the Swagger UI now contains blocks to make it easier to overwrite certain parts.
  See the official documentation how to do this.

3.2.0 (2018-03-24)
------------------

* Add a documentation form extension. Use the ``documentation`` option to define how a form field is documented.
* Allow references to config definitions in controllers.
* Using `@Model` implicitely in `@SWG\Schema`, `@SWG\Items` and `@SWG\Property` is deprecated. Use `ref=@Model()` instead.

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

3.1.0 (2018-01-28)
------------------

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

3.0.0 (2017-12-10)
------------------

Large refactoring introducing `zircote/swagger-php` for swagger annotations.

See UPGRADE-3.0.md for upgrading instructions.
