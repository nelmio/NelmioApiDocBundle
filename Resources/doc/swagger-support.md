Swagger Support
---------------

It is possible to make your application produce Swagger-compliant JSON output
based on `@ApiDoc` annotations, which can be used for consumption by
[swagger-ui](https://github.com/wordnik/swagger-ui).

### Annotation options

A couple of properties has been added to `@ApiDoc`:

To define a __resource description__:

```php
<?php

	/**
     * @ApiDoc(
     *     resource=true,
     *     resourceDescription="Operations on users.",
     *     description="Retrieve list of users."
     *  )
     */
	public function listUsersAction()
    {
          /* Stuff */
    }

```

The `resourceDescription` is distinct from `description` as it applies to the
whole resource group and not just the particular API endpoint.

#### Defining a form-type as a GET form

If you use forms to capture GET requests, you will have to specify the
`paramType` to `query` in the annotation:

```php

<?php

/**
 * @ApiDoc(
 *    input = {"class" = "Foo\ContentBundle\Form\SearchType", "paramType" = "query"},
 *   ...
 * )
 */

 public function searchAction(Request $request)
 {
```

### Multiple response models

Swagger provides you the ability to specify alternate output models for
different status codes. Example, `200` would return your default resource object
in JSON form, but `400` may return a custom validation error list object. This
can be specified through the `responseMap` property:

```php
<?php

	/**
     * @ApiDoc(
     *     description="Retrieve list of users.",
     *     statusCodes={
     *         400 = "Validation failed."
     *     },
     *     responseMap={
     *     	200 = "FooBundle\Entity\User",
     *         400 = {
     *             "class"="CommonBundle\Model\ValidationErrors",
     *             "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *         }
     *     }
     *  )
     */
	public function updateUserAction()
    {
          /* Stuff */
    }

```

This will tell Swagger that `CommonBundle\Model\ValidationErrors` is returned when this endpoint returns a `400 Validation failed.` HTTP response.

__Note:__ You can omit the `200` entry in the `responseMap` property and specify the default `output` property instead. That will result on the same thing.

### Integration with `wordnik/swagger-ui`

You could import the routes for use with [`swagger-ui`](https://github.com/wordnik/swagger-ui):

```yml
#app/config/routing.yml

nelmio_api_swagger:
    resource: "@NelmioApiDocBundle/Resources/config/swagger_routing.yml"
    prefix: /api-docs
```

Et voila!, simply specify http://yourdomain.com/api-docs in your `swagger-ui` instance and you are good to go.

__Note__: If your `swagger-ui` instance does not live under the same domain, you
will probably encounter some problems related to same-origin policy violations.
[NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle) can solve this
problem for you. Read through how to allow cross-site requests for the
`/api-docs/*` pages.

### Dumping the Swagger-compliant JSON API definitions

To display all JSON definitions:

```
php app/console api:swagger:dump
```

To dump just the resource list:

```
php app/console api:swagger:dump --list-only
```

To dump just the API definition the `users` resource:

```
php app/console api:swagger:dump --resource=users
```

Specify the `--pretty` flag to display a prettified JSON output.

#### Dump to files

You can specify the destination if you wish to dump the JSON definition to a file:

```
php app/console api:swagger:dump --list-only swagger-docs/api-docs.json
php app/console api:swagger:dump --resource=users swagger-docs/users.json
```

Or, you can dump everything into a directory in one command:

```
php app/console api:swagger:dump swagger-docs
```

### Model naming

By default, the model naming strategy used is the `dot_notation` strategy. The
model IDs are simply the Fully Qualified Class Name (FQCN) of the class
associated to it, with the `\` replaced with `.`:

`Vendor\UserBundle\Entity\User => Vendor.UserBundle.Entity.User`

You can also change the `model_naming_strategy` in the configuration to
`last_segment_only`, if you want model IDs to be just the class name minus the
namespaces (`Vendor\UserBundle\Entity\User => User`). This will not afford you
the guarantee that model IDs are unique, but that would really just depend on
the classes you have in use.

---

[back to index](index.md)
