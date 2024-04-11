# Contributing

First of all, **thank you** for contributing, **you are awesome**! With your contribution, you not only improve this
bundle, but also become part of a [great community](https://github.com/nelmio/NelmioApiDocBundle/graphs/contributors)
maintaining it.

## Guidelines

Here are a few guidelines to follow in order to ease code reviews, and discussions before maintainers can accept and
merge your work. Thank you!

The key words "MUST", "MUST NOT", "REQUIRED", "SHALL", "SHALL NOT", "SHOULD", "SHOULD NOT",
"RECOMMENDED",  "MAY", and "OPTIONAL" in this document are to be interpreted as described in
[RFC 2119](https://datatracker.ietf.org/doc/html/rfc2119).

### Code Style

Code MUST adhere to all rules outlined in the
[Symfony Coding Standards](https://symfony.com/doc/current/contributing/code/standards.html) as
defined by the [@Symfony](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/Symfony.rst) rule set,
utilized by the [PHP-CS-Fixer](https://cs.symfony.com) tool.

You SHOULD run `composer run phpcs-check` to check for any violations. You SHOULD run `composer run phpcs-fix` to
fix any potential issues.

### Code Quality

You MUST use the static analysis tool [PHPStan](https://phpstan.org/) to analyse any newly added or revised code within
this bundle.

You MUST run `composer run phpstan` to check for any violations. You MUST fix all violations related to any newly added
or revised code.

### Tests

You MUST write (or update) unit and/or functional tests for any newly added or revised functionality within this bundle.

You MUST validate newly added or revised tests by running `composer run phpunit`.

### Documentation

You SHOULD write (or update) documentation.

You SHOULD write
[commit messages that make sense](https://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).

You MUST [rebase your branch](https://git-scm.com/book/en/v2/Git-Branching-Rebasing) before submitting your Pull Request.

While creating your Pull Request on GitHub, you MUST write a description which gives the context and/or explains why you
are creating it.
