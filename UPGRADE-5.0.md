# Upgrading From 4.x To 5.0

## BC BREAK: Bumped minimum PHP version to 8.1

## Bumped minimum Symfony version to 6.4

## Dropped support for Api Platform 2

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

## BC BREAK: Removed passing an indexed array with a collection of path patterns as argument 1 for `Nelmio\ApiDocBundle\Routing\FilteredRouteCollectionBuilder::__construct()`

## BC BREAK: Updated `PropertyDescriberInterface::describe()` signature
```diff
- public function describe(array $types, Schema $property, ?array $groups = null /* , ?Schema $schema = null */ /* , array $context = [] */);
+ public function describe(array $types, Schema $property, array $context = []);
```

`$groups` are passed in `$context` and can be accessed via `$context['groups']`.

`$schema` has been removed with no replacement.

## BC BREAK: Updated `PropertyDescriberInterface::supports()` signature
Future proofing for potential future changes and keeping it consistent with `describe()`.
```diff
- public function supports(array $types): bool;
+ public function supports(array $types, array $context = []): bool;
```

## BC BREAK: `Nelmio\ApiDocBundle\Command` has been made final

## BC BREAK: Made classes implementing  `Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface` final
- `Nelmio\ApiDocBundle\PropertyDescriber\ArrayPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\BooleanPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\DateTimePropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\CompoundPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\FloatPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\IntegerPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\ObjectPropertyDescriber`
- `Nelmio\ApiDocBundle\PropertyDescriber\StringPropertyDescriber`

## BC BREAK: Added `void` return type to:
- `Nelmio\ApiDocBundle\Describer\DescriberInterface::describe()`
- `Nelmio\ApiDocBundle\Describer\ExternalDocDescriber::describe()`
- `Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface::describe()`
- `Nelmio\ApiDocBundle\ModelDescriber\FallbackObjectModelDescriber::describe()`
- `Nelmio\ApiDocBundle\ModelDescriber\JMSModelDescriber::describe()`
- `Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface::setModelRegistry()`
- `Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait::setModelRegistry()`
- `Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface::describe()`
- `Nelmio\ApiDocBundle\RouteDescriber\RouteDescriberInterface::describe()`

## BC BREAK: `Nelmio\ApiDocBundle\Attribute\Area::__construct()` 1st parameter `$properties` has been changed:
- `$properties` has been renamed to `$areas`
```diff
-#[Areas(properties: ['foo', 'bar'])]
+#[Areas(areas: ['foo', 'bar'])]
```
- `$properties` no longer allows an array key `value` with a list of strings, pass a list of string instead
```diff
-/** @param string[]|array{value: string[]} $properties */
-#[Areas(properties: ['value' => ['foo', 'bar']])]
-[Areas(['value' => ['foo', 'bar']])]
+/** @param string[] $areas */
+#[Areas(areas: ['foo', 'bar'])]
+#[Areas(['foo', 'bar'])]
```