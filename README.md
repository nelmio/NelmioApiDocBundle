NelmioApiDocBundle
==================

[![Build
Status](https://secure.travis-ci.org/nelmio/NelmioApiDocBundle.png?branch=master)](http://travis-ci.org/nelmio/NelmioApiDocBundle)

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation
for your APIs.

**Important:** This bundle is developed in sync with [symfony's
repository](https://github.com/symfony/symfony).
For Symfony `2.0.x`, you need to use the `1.*` version of the bundle.


Documentation
-------------

For documentation, see:

    Resources/doc/

[Read the documentation](https://github.com/nelmio/NelmioApiDocBundle/blob/master/Resources/doc/index.md)


Contributing
------------

See
[CONTRIBUTING](https://github.com/nelmio/NelmioApiDocBundle/blob/master/CONTRIBUTING.md)
file.


Running the Tests
-----------------

Install the [Composer](http://getcomposer.org/) `dev` dependencies:

    php composer.phar install --dev

Then, run the test suite using
[PHPUnit](https://github.com/sebastianbergmann/phpunit/):

    phpunit


Credits
-------

The design is heavily inspired by the
[swagger-ui](https://github.com/wordnik/swagger-ui) project.
Some icons from the [Glyphicons](http://glyphicons.com/) library are used to
render the documentation.


License
-------

This bundle is released under the MIT license. See the complete license in the
bundle:

    Resources/meta/LICENSE
