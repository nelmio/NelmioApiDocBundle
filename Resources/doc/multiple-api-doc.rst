Multiple API Documentation ("Views")
====================================

With the ``views`` tag in the ``@ApiDoc`` annotation, it is possible to create
different views of your API documentation. Without the tag, all methods are
located in the ``default`` view, and can be found under the normal API
documentation url.

You can specify one or more _view_ names under which the method will be
visible.

An example::

    /**
     * A resource
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of your API method",
     *  views = { "default", "premium" }
     * )
     */
    public function getAction()
    {
    }

    /**
     * Another resource
     *
     * @ApiDoc(
     *  resource=true,
     *  description="This is a description of another API method",
     *  views = { "premium" }
     * )
     */
    public function getAnotherAction()
    {
    }

In this case, only the first resource will be available under the default view,
while both methods will be available under the ``premium`` view.

#### Configuring Views

Each view can be configured.
```yaml
# app/config/config.yml
nelmio_api_doc:
    views:
      premium:
        include: ["code", "platinum", "secret"]
        include_empty: true
```

* `include` will include into the `premium` view all the endpoints that belongs to "code", "platinum", "secret" views.
* `include_empty` will include into the `premium` all the endpoints has no specified views

Accessing Specific API Views
----------------------------
The `default` view can be found at the normal location. Other views can be
The ``default`` view can be found at the normal location. Other views can be
found at ``http://your.documentation/<view name>``.

For instance, if your documentation is located at:

.. code-block:: text

        http://example.org/doc/api/v1/

then the ``premium`` view will be located at:

.. code-block:: text

        http://example.org/doc/api/v1/premium
