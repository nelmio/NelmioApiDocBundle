# Upgrading From 4.x To 5.0

## BC BREAK: Bumped minimum PHP version to 8.1

## BC BREAK: Removed support for annotations
Upgrade to PHP 8.1 attributes.

```diff
...
- use OpenApi\Annotations as OA;
+ use OpenApi\Attributes as OA;
...
-/**
- * @OA\Property(type="integer", format="negative-int")
- */
+#[OA\Property(type: "integer", format: "negative-int")]
public int $negative;
...
```

This causes the following breaking changes in classes that used on annotations:
- BC BREAK: Removed 3rd parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber::__construct()`