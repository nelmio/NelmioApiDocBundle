# Upgrading From 2.x To 3.0

NelmioApiDocBundle has been entirely refactored in 3.0 to focus on Swagger
and most of it has changed.
However, we tried to keep its API as familiar as possible: the `@ApiDoc`
annotation is kept and the bundle remains the same (it is required the same
way it was in 2.0).

## Upgrade Your Annotations

Some fields of the `@ApiDoc` annotation were removed as they are no
longer used by the bundle:

- `section`
- `views`
- `host`
- `cache`
- `resource`
- `resourceDescription`
- `https`, add a scheme requirement to your route instead
- `documentation`, use `description` instead
