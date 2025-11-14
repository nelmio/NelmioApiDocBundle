Model (Class) Customization
===========================

The bundle uses a various model describers to generate the OpenAPI
schema for your models. You can create your own model describer to
customize how your models are represented in the documentation.

This is useful when you have custom value objects or when you want to represent
your models in a different way than the default describers do.

.. note::

    Model describers are used for entire models (classes), while property describers
    are used for the individual properties of a model. A model will also be documented in the generated
    ``components.schemas`` section of the OpenAPI documentation, while properties are
    documented directly in the schema.

Creating a custom Model Describer
---------------------------------

To create a custom model describer, you need to create a class that implements the `ModelDescriberInterface`_.
This interface has two methods:

* ``supports(Model $model): bool``: This method should return ``true`` if your describer can handle the given model.
* ``describe(Model $model, Schema $schema): void``: This method should populate the OpenAPI ``Schema`` for the given model.

For example, let's say you have a ``Money`` value object that you
want to represent as a string with a specific format in your API documentation:

.. code-block:: php

    namespace App\Entity;

    class Money
    {
        public int $cents;
        public string $currency;
    }

You can create a custom model describer for this ``Money`` class like this:

.. code-block:: php

    namespace App\ModelDescriber;

    use App\Entity\Money;
    use Nelmio\ApiDocBundle\Model\Model;
    use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
    use OpenApi\Annotations\Schema;
    use Symfony\Component\TypeInfo\Type\ObjectType;

    class MoneyModelDescriber implements ModelDescriberInterface
    {
        public function describe(Model $model, Schema $schema): void
        {
            $schema->type = 'string';
            $schema->example = '12.34 EUR';
            $schema->description = 'A monetary value represented as a string.';
        }

        public function supports(Model $model): bool
        {
            $type = $model->getTypeInfo();
            if (!$type instanceof ObjectType) {
                return false;
            }

            return Money::class === $type->getClassName();
        }
    }

Registering the custom Model Describer
--------------------------------------

If you are using Symfony's default ``services.yaml`` configuration, your custom
model describer will be automatically registered and tagged thanks to autoconfiguration!

If you're not using ``autoconfigure`` or if you need to set a priority to make sure your describer runs before or after
other describers, you can configure it manually in your ``services.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\ModelDescriber\MoneyModelDescriber:
                tags:
                    # register the model describer with a high priority (called earlier)
                    - { name: 'nelmio_api_doc.model_describer', priority: 500 }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\ModelDescriber\MoneyModelDescriber;

        return function(ContainerConfigurator $container) {
            // ...

            // if you're using autoconfigure, the tag will be automatically applied
            $services->set(App\ModelDescriber\MoneyModelDescriber::class)
                // register the model describer with a high priority (called earlier)
                ->tag('nelmio_api_doc.model_describer', [
                    'priority' => 500,
                ])
            ;
        };

Self-Describing Models
----------------------

Another way to customize model documentation is by implementing the
`SelfDescribingModelInterface`_. This is
particularly useful when the model itself is the best place to hold its
documentation.

If your model implements this interface, its ``describe`` method will be called to populate the schema.

.. code-block:: php

    namespace App\Entity;

    use Nelmio\ApiDocBundle\Model\Model;
    use Nelmio\ApiDocBundle\ModelDescriber\SelfDescribingModelInterface;
    use OpenApi\Annotations\Schema;

    class Money implements SelfDescribingModelInterface
    {
        public static function describe(Schema $schema, Model $model): void
        {
            $schema->type = 'string';
            $schema->example = '12.34 EUR';
            $schema->description = 'A monetary value represented as a string.';
        }
    }

Example Output
--------------
With the above customizations, the generated ``components.schemas`` section
will include the following definition for the ``Money`` model:

.. configuration-block::

    .. code-block:: json

        {
            "components": {
                "schemas": {
                    "Money": {
                        "type": "string",
                        "example": "12.34 EUR",
                        "description": "A monetary value represented as a string."
                    }
                }
            }
        }

    .. code-block:: yaml

        components:
            schemas:
                Money:
                    type: string
                    example: "12.34 EUR"
                    description: "A monetary value represented as a string."

.. _ModelDescriberInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/ModelDescriber/ModelDescriberInterface.php
.. _SelfDescribingModelInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/ModelDescriber/SelfDescribingModelInterface.php