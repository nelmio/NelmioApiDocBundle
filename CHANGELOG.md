CHANGELOG
=========

3.1.0 (unreleased)
------------------

Symfony Forms
* Support for boolean checkbox
* Support for integer

JMS Serializer
* Support JMS `int` (alias for `integer`)
* Also process phpdoc annotations (if `phpdocumentor/reflection-docblock` is available)

SwaggerPHP
* Handle `enum` and `default` properties from SwaggerPHP annotation

3.0.0 (2017-12-10)
------------------

Large refactoring introducing `zircote/swagger-php` for swagger annotations.

See UPGRADE-3.0.md for upgrading instructions.
