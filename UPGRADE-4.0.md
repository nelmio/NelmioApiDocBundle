Upgrading From 3.x To 4.0
=========================

Version 4 is a major change introducing OpenAPI 3.0 support, the rebranded swagger specification, which brings a set of new interesting features. Unfortunately this required a rework to a large part of the bundle, and introduces BC breaks.

The Visual guide to "[What's new in 3.0 spec](https://blog.readme.com/an-example-filled-guide-to-swagger-3-2/)" gives more information on OpenApi 3.0.

Version 4 does not support older versions of the specification. If you need to output swagger v2 documentation, you will need to use the latest 3.x release.

The Upgrade to Swagger 3.0
--------------------------

The biggest part of the upgrade will most likely be the upgrade of the library `zircote/swagger-php` to `3.0` which introduces new annotations in order to support OpenAPI 3.0 but also changes
their namespace from ``Swagger`` to ``OpenApi``.

They created a dedicated page to help you upgrade : https://zircote.github.io/swagger-php/Migrating-to-v3.html.

Here are some additional advices that are more likely to apply to NelmioApiDocBundle users:

- Upgrade all your ``use Swagger\Annotations as SWG`` statements to ``use OpenApi\Annotations as OA;`` (to simplify the upgrade you may also stick to the ``SWG`` aliasing).
  In case you changed ``SWG`` to ``OA``, upgrade all your annotations from ``@SWG\...`` to ``@OA\...``.

- Update your config in case you used inlined swagger docummentation (the field ``nelmio_api_doc.documentation``). [A tool](https://openapi-converter.herokuapp.com/) is available to help you convert it.

- In case you used ``@OA\Response(..., @OA\Schema(...))``, you should explicit your media type by using the annotation ``@OA\JsonContent`` or ``@OA\XmlContent`` instead of ``@OA\Schema``:
  ``@OA\Response(..., @OA\JsonContent(...))`` or ``@OA\Response(..., @OA\XmlContent(...))``.

  When you use ``@Model`` directly (``@OA\Response(..., @Model(...)))``), the media type is set by default to ``json``.

BC Breaks
---------

There are also BC breaks that were introduced in 4.0:

- We migrated from `EXSyst\Component\Swagger\Swagger` to `OpenApi\Annotations\OpenApi` to describe the api in all our describers signature (`DescriberInterface`, `RouteDescriberInterface`, `ModelDescriberInterface`, `PropertyDescriberInterface`).

- `PropertyDescriberInterface` now takes several types as input to leverage compound types support in OpenApi 3.0 (`int|string` for instance).
