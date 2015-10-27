Commands
--------

A command is provided in order to dump the documentation in `json`, `markdown`,
or `html`.

    php app/console api:doc:dump [--format="..."]

The `--format` option allows to choose the format (default is: `markdown`).

For example to generate a static version of your documentation you can use:

    php app/console api:doc:dump --format=html > api.html

By default, the generated HTML will add the sandbox feature if you didn't
disable it in the configuration.  If you want to generate a static version of
your documentation without sandbox, use the `--no-sandbox` option.

---

[back to index](index.md)
