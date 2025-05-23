# NelmioApiDocBundle

[![Build Status](https://img.shields.io/github/check-runs/nelmio/NelmioApiDocBundle/5.x?style=flat-square)](https://github.com/nelmio/NelmioApiDocBundle/actions?query=workflow:CI)
[![Codecov](https://img.shields.io/codecov/c/github/nelmio/NelmioApiDocBundle/5.x?style=flat-square)](https://app.codecov.io/gh/nelmio/NelmioApiDocBundle/tree/5.x)
[![Total Downloads](https://img.shields.io/packagist/dt/nelmio/api-doc-bundle?style=flat-square)](https://packagist.org/packages/nelmio/api-doc-bundle)
[![Latest Stable Version](https://img.shields.io/packagist/v/nelmio/api-doc-bundle?label=stable&style=flat-square)](https://packagist.org/packages/nelmio/api-doc-bundle)
[![PHP Version](https://img.shields.io/packagist/dependency-v/nelmio/api-doc-bundle/PHP?style=flat-square)](https://packagist.org/packages/nelmio/api-doc-bundle)
[![License](https://img.shields.io/github/license/nelmio/NelmioApiDocBundle?style=flat-square)](LICENSE)

The **NelmioApiDocBundle** bundle allows you to generate a decent documentation
for your APIs.

## Installation

Open a command console, enter your project directory and execute the following command to download the latest version of this bundle:

```
composer require nelmio/api-doc-bundle
```

## Documentation

[Read the documentation on symfony.com](https://symfony.com/doc/current/bundles/NelmioApiDocBundle/index.html)

### Migrate from 4.x to 5.0

[To migrate from 4.x to 5.0, follow our guide.](https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/UPGRADE-5.0.md)

This version comes with the following major changes:
- The bundle now requires PHP 8.1 or higher.
- Support for annotations has been removed in favor of PHP 8 attributes.
- Minimum Symfony version is now 6.4.
- Major cleanup and simplification of the codebase.

### Migrate from 3.x to 4.0

[To migrate from 3.x to 4.0, follow our guide.](https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/UPGRADE-4.0.md)

Version 4.0 brings OpenAPI 3.0 support. If you want to stick to Swagger 2.0, you should use the version 3 of this bundle.

### Migrate from 2.x to 3.0

[To migrate from 2.x to 3.0, follow our guide.](https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/UPGRADE-3.0.md)

## Contributing

See
[CONTRIBUTING](https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/CONTRIBUTING.md)
file.

### Running the Tests

Install the [Composer](http://getcomposer.org/) dependencies:

    git clone https://github.com/nelmio/NelmioApiDocBundle.git
    cd NelmioApiDocBundle
    composer update

Then run the test suite:

    composer phpunit

## Sponsored by
<p align=center>
    <a href="https://jb.gg/OpenSourceSupport">
        <img alt="JetBrains logo" src="https://resources.jetbrains.com/storage/products/company/brand/logos/jetbrains.svg" />
    </a>
    <p align=center>
        JetBrains sponsors this project by providing an open-source license for their tools.
    </p>
</p>

## License

[This bundle is released under the MIT license.](LICENSE)
