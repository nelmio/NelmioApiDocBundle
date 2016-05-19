The ``ApiDoc()`` Annotation
===========================

The bundle provides an ``ApiDoc()`` annotation for your controllers::

    namespace Your\Namespace;

    use Nelmio\ApiDocBundle\Annotation\ApiDoc;

    class YourController extends Controller
    {
        /**
         * This is the documentation description of your method, it will appear
         * on a specific pane. It will read all the text until the first
         * annotation.
         *
         * @ApiDoc(
         *  resource=true,
         *  description="This is a description of your API method",
         *  filters={
         *      {"name"="a-filter", "dataType"="integer"},
         *      {"name"="another-filter", "dataType"="string", "pattern"="(foo|bar) ASC|DESC"}
         *  }
         * )
         */
        public function getAction()
        {
        }

        /**
         * @ApiDoc(
         *  description="Create a new Object",
         *  input="Your\Namespace\Form\Type\YourType",
         *  output="Your\Namespace\Class"
         * )
         */
        public function postAction()
        {
        }

        /**
         * @ApiDoc(
         *  description="Returns a collection of Object",
         *  requirements={
         *      {
         *          "name"="limit",
         *          "dataType"="integer",
         *          "requirement"="\d+",
         *          "description"="how many objects to return"
         *      }
         *  },
         *  parameters={
         *      {"name"="categoryId", "dataType"="integer", "required"=true, "description"="category id"}
         *  }
         * )
         */
        public function cgetAction($limit)
        {
        }
    }

The following properties are available:

* ``section``: allow to group resources
* ``resource``: whether the method describes a main resource or not (default:
  ``false``);
* ``description``: a description of the API method;
* ``https``: whether the method described requires the https protocol (default:
  ``false``);
* ``deprecated``: allow to set method as deprecated (default: ``false``);
* ``tags``: allow to tag a method (e.g. ``beta`` or ``in-development``). Either
  a single tag or an array of tags. Each tag can have an optional hex colorcode
  attached.

.. code-block:: php

    class YourController
    {
        /**
         * @ApiDoc(
         *     tags={
         *         "stable",
         *         "deprecated" = "#ff0000"
         *     }
         * )
         */
        public function myFunction()
        {
            // ...
        }
    }

* ``filters``: an array of filters;
* ``requirements``: an array of requirements;
* ``parameters``: an array of parameters;
* ``headers``: an array of headers; available properties are: ``name``, ``description``, ``required``, ``default``. Example:

.. code-block:: php

    class YourController
    {
        /**
         * @ApiDoc(
         *     headers={
         *         {
         *             "name"="X-AUTHORIZE-KEY",
         *             "description"="Authorization key"
         *         }
         *     }
         * )
         */
        public function myFunction()
        {
            // ...
        }
    }

* ``input``: the input type associated to the method (currently this supports
  Form Types, classes with JMS Serializer metadata, classes with Validation
  component metadata and classes that implement JsonSerializable) useful for
  POST|PUT methods, either as FQCN or as form type (if it is registered in the
  form factory in the container).
* ``output``: the output type associated with the response.  Specified and
  parsed the same way as ``input``.
* ``statusCodes``: an array of HTTP status codes and a description of when that
  status is returned; Example:

.. code-block:: php

    class YourController
    {
        /**
         * @ApiDoc(
         *     statusCodes={
         *         200="Returned when successful",
         *         403="Returned when the user is not authorized to say hello",
         *         404={
         *           "Returned when the user is not found",
         *           "Returned when something else is not found"
         *         }
         *     }
         * )
         */
        public function myFunction()
        {
            // ...
        }
    }

* ``views``: the view(s) under which this resource will be shown. Leave empty to
  specify the default view. Either a single view, or an array of views.

Each *filter* has to define a ``name`` parameter, but other parameters are free.
Filters are often optional parameters, and you can document them as you want,
but keep in mind to be consistent for the whole documentation.

If you set ``input``, then the bundle automatically extracts parameters based on
the given type, and determines for each parameter its data type, and if it's
required or not.

For classes parsed with JMS metadata, description will be taken from the
properties doc comment, if available.

For Form Types, you can add an extra option named ``description`` on each field::

    class YourType extends AbstractType
    {
        /**
         * {@inheritdoc}
         */
        public function buildForm(FormBuilder $builder, array $options)
        {
            $builder->add('note', null, array(
                'description' => 'this is a note',
            ));

            // ...
        }
    }

The bundle will also get information from the routing definition
(``requirements``, ``path``, etc), so to get the best out of it you should
define strict methods requirements etc.
