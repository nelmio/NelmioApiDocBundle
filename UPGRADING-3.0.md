# Upgrading From 2.x To 3.0

NelmioApiDocBundle has been entirely refactored in 3.0 to focus on Swagger
and most of it has changed.

## Upgrade Your Annotations

The `@ApiDoc` annotation has been removed and you must now use
[Swagger-php](https://github.com/zircote/swagger-php) annotations.

An upgrade example:
```php
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class YourController extends Controller
{
    /**
     * This is a description of your API method.
     *
     * @ApiDoc(
     *  filters={
     *      {"name"="a-filter", "dataType"="integer"},
     *      {"name"="another-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}
     *  }
     * )
     */
    public function getAction()
    {
    }
}
```

will become:
```php
use Swagger\Annotations as SWG;

class YourController extends Controller
{
    /**
     * This is a description of your API method.
     *
     * @SWG\Parameter(
     *     name="a-filter",
     *     in="query",
     *     type="integer"
     * )
     * @SWG\Parameter(
     *     name="another-filter",
     *     in="query",
     *     type="string",
     *     format="(foo|bar) ASC|DESC"
     * )
     */
    public function getAction()
    {
    }
}
```
