NelmioApiDocBundle
===================

##Swagger support##

It is now possible to make your application produce Swagger-compliant JSON output based on `@ApiDoc` annotations, which can be used for consumption by [swagger-ui](https://github.com/wordnik/swagger-ui).

###Annotations###

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

The `resourceDescription` is distinct from `description` as it applies to the whole resource group and not just the particular API endpoint.

Swagger provides you the ability to specify alternate output models for different status codes. Example, `200` would return your default resource object in JSON form, but `400` may return a custom validation error list object. This can be specified through the `responseMap` property:

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

###wordnik/swagger-ui consumption

You could import the routes for use with [`swagger-ui`](https://github.com/wordnik/swagger-ui):

```yml
#app/config/routing.yml

nelmio_api_swagger:
    resource: "@NelmioApiDocBundle/Resources/config/swagger_routing.yml"
    prefix: /api-docs
```

Et voila!, simply specify http://yourdomain.com/api-docs in your Swagger client and you are good to go.

### Generating Swagger documentation JSON files

You might need the Swagger documentation as file(s) for a different purpose. You can dump them into the file-system using the `api:swagger:dump` command:

```
php app/console api:swagger:dump --all app/Resources/swagger-docs
```

The above command will dump JSON files under the `app/Resources/swagger-docs` directory (relative to your project root), and you can now process or serve these files however you want. The destination defaults to the project root if not specified.

#### Selective dumps

Dump the `api-docs.json` resource list file only:
```
php app/console api:swagger:dump --list-only
```

Dump a specific resource API declaration only:
```
php app/console api:swagger:dump --resource=users
```
The above command will dump the `/users` API declaration in a `users.json` file.
