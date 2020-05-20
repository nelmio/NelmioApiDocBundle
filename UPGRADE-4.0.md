Upgrading From 3.x To 4.0
=========================

Version 4.0 introduced OpenAPI 3.0 support which brings a set of new interesting features.
However it required to rework a big part of the bundle, and it introduced BC breaks.

The Upgrade to Swagger 3.0
--------------------------

The biggest part of the upgrade will most likely be the upgrade of the library `zircote/swagger-php` to `3.0` which introduces new annotations in order to support OpenAPI 3.0 but also changes
their namespace from ``Swagger`` to ``OpenApi``.

They created a dedicated page to help you upgrade : https://zircote.github.io/swagger-php/Migrating-to-v3.html.

Here are some additional advices that are more likely to apply to NelmioApiDocBundle users:

- Upgrade all your ``use Swagger\Annotations as SWG`` statements to ``use OpenApi\Annotations as OA;`` (to simplify the upgrade you may also stick to the ``SWG`` aliasing).
  In case you changed ``SWG`` to ``OA``, upgrade all your annotations from ``@SWG\...`` to ``@OA\...``.

- Update your config in case you used inlined swagger docummentation (the field ``nelmio_api_doc.documentation``). [A tool](https://openapi-converter.herokuapp.com/) is available to help you convert it.

- In case you use ``@OA\Response(..., @OA\Schema(...))``, you should wrap ``@Schema`` in an annotation to explicit the media type:
  ``@OA\Response(..., @OA\JsonContent(@OA\Schema(...)))`` or ``@OA\Response(..., @OA\XmlContent(@OA\Schema(...)))``.

  When you use ``@Model`` directly (``@OA\Response(..., @Model(...)))``), the media type is set by default to ``json``.
