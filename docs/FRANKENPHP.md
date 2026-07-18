# FrankenPHP worker mode

NelmioApiDocBundle is compatible with **FrankenPHP worker mode** (and other long-lived PHP runtimes such as RoadRunner or Swoole with Symfony's kernel reset).

FrankenPHP worker mode: **Supported**.

## What was adapted

### Safe in-memory OpenAPI cache (`ApiDocGenerator`)

`ApiDocGenerator` keeps the generated OpenAPI document in memory after the first successful generation. That is intentional in worker mode: documentation is application state, not request state, so warming it once is a performance win.

To stay worker-safe:

- The in-memory document is assigned **only after** generation succeeds, so a failed generation cannot poison later requests in the same worker.
- `ApiDocGenerator` implements Symfony [`ResetInterface`](https://symfony.com/doc/current/components/dependency_injection.html#resetting-container) (`reset()` clears the in-memory document). It is **not** tagged with `kernel.reset` by default, so Symfony does not discard the warm cache between requests.
- If you need to force regeneration every request (unusual), tag the generator yourself:

```yaml
# config/services.yaml
services:
    nelmio_api_doc.generator.default:
        tags:
            - { name: kernel.reset, method: reset }
```

### Ephemeral describer state

These services hold short-lived working state and **are** tagged with `kernel.reset`:

| Service | State cleared on `reset()` |
| --- | --- |
| `nelmio_api_doc.object_model.property_describer` | Recursion helper used while describing properties |
| `nelmio_api_doc.model_describers.jms` (optional) | JMS serialization contexts / metadata stacks |
| `nelmio_api_doc.model_describers.jms.bazinga_hateoas` (optional) | Forwards reset to the inner JMS describer |

`PropertyDescriber` also clears its recursion helper in a `finally` block so an exception mid-description cannot leak into the next request.

## Recommended production config

A PSR-6 cache pool is still recommended so documentation survives worker restarts and is shared across workers:

```yaml
# config/packages/nelmio_api_doc.yaml
nelmio_api_doc:
    cache:
        pool: cache.system
    # ...
```

## Verification

1. Run your app under FrankenPHP with worker enabled (production Caddyfile / `php_server { worker ... }`).
2. Hit the documentation JSON/UI endpoints repeatedly (first request may be slower; later ones should reuse the in-memory document).
3. Confirm responses stay consistent and that a transient generation error does not stick after the next successful request.
