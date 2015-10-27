DunglasApiBundle Support
------------------------

This bundle recognizes and documents resources exposed with
[DunglasApiBundle](https://github.com/dunglas/DunglasApiBundle).

Install NelmioApiDocBundle and the documentation will be automatically
available. To enable the sandbox, use the following configuration:

```yaml
# app/config/config.yml
nelmio_api_doc:
    sandbox:
        accept_type:        "application/json"
        body_format:
            formats:        [ "json" ]
            default_format: "json"
        request_format:
            formats:
                json:       "application/json"
```

---

[back to index](index.md)
