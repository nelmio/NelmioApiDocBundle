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

This causes the following breaking changes in classes that used annotations:
- BC BREAK: Removed 3rd parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber::__construct()`
- BC BREAK: Removed 2nd parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\ModelDescriber\FormModelDescriber::__construct()`
- BC BREAK: Removed 2nd parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber::__construct()`
- BC BREAK: Removed 2nd parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\ModelDescriber\ObjectModelDescriber::__construct()`
- BC BREAK: Removed 1st parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\RouteDescriber\FosRestDescriber::__construct()`
- BC BREAK: Removed 1st parameter `?Reader $annotationReader` from `Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder::__construct()`

## BC BREAK: `Nelmio\ApiDocBundle\Annotation` namespace has been remove in favor of `Nelmio\ApiDocBundle\Attribute`

## BC BREAK: Configuration option `with_annotation` has been renamed to `with_attribute`
```diff
nelmio_api_doc:
    areas:
        path_patterns:
            - ^/api/foo
-       with_annotation: true
+       with_attribute: true
```

## BC BREAK: Removed `Nelmio\ApiDocBundle\PropertyDescriber\NullablePropertyTrait`
This class was deprecated since `4.17.0`

## Removed optional 4th param `bool $overwrite = false` from `Nelmio\ApiDocBundle\Describer\OpenApiPhpDescriber::__construct()`
This parameter was deprecated since `4.25.2`

## BC BREAK: Removed `Nelmio\ApiDocBundle\PropertyDescriber\RequiredPropertyDescriber`

## BC BREAK: Removed `Nelmio\ApiDocBundle\Form\Extension::getExtendedType()`

## BC BREAK: Removed `null` as a possible type for parameter `$options` in `Nelmio\ApiDocBundle\Model\Model::__construct()` & `Nelmio\ApiDocBundle\Attribute\Model::__construct()`

## BC BREAK: Removed `Nelmio\ApiDocBundle\Exception\UndocumentedArrayItemsException`

## BC BREAK: Changed type of parameter `$propertyDescriber` in `Nelmio\ApiDocBundle\ModelDescriber\ObjectModelDescriber::__construct()` from `PropertyDescriberInterface|PropertyDescriberInterface[]` to `PropertyDescriberInterface`