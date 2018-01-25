CHANGELOG
=========

3.1.0 (unreleased)
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
