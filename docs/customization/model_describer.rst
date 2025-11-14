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

.. note::

    Model describers are **not** chained like :doc:`type describers </customization/type_describer>`.
    Only one model describer will be used for a given model. If multiple describers
    support the same model, the one with the highest priority will be used.

For example, let's say you have a ``Money`` value object that you
want to represent as a string with a specific format in your API documentation:

.. code-block:: php

    namespace App\Entity;

    class Money
    {
        public int $cents;
        public string $currency;
    }

Creating a custom Model Describer
---------------------------------

To create a custom model describer, you need to create a class that implements the `ModelDescriberInterface`_.
This interface has two methods:

* ``supports(Model $model): bool``: This method should return ``true`` if your describer can handle the given model.
* ``describe(Model $model, Schema $schema): void``: This method should populate the OpenAPI ``Schema`` for the given model.

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

Decorating Built-in Model Describers
_________________

NelmioApiDocBundle also provides various built-in model describers.

You can decorate these describers to extend or modify their behavior.

For example, if you want to customize how enums are represented
(for example, to help with client code generation), you can create a
custom model describer that decorates or replaces the built-in enum describer.

.. code-block:: php

    namespace App\Entity;

    enum Status: string
    {
        case ACTIVE = 'active';
        case INACTIVE = 'inactive';
        case PENDING = 'pending';
    }

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            App\ModelDescriber\MyEnumDescriber:
                decorates: 'nelmio_api_doc.model_describer.enum'
                # pass the old service as an argument
                arguments: ['@.inner']

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\ModelDescriber\MyEnumDescriber;

        return function(ContainerConfigurator $container): void {
            // ...

            $services->set(MyEnumDescriber::class)
                ->decorate('nelmio_api_doc.model_describer.enum')
                // pass the old service as an argument
                ->args([service('.inner')]);
        };

.. code-block:: php

    namespace App\ModelDescriber;

    use Nelmio\ApiDocBundle\Model\Model;
    use Nelmio\ApiDocBundle\ModelDescriber\ModelDescriberInterface;
    use OpenApi\Annotations\Schema;
    use Symfony\Component\TypeInfo\Type\EnumType;

    class MyEnumDescriber implements ModelDescriberInterface
    {
        public function __construct(
            private ModelDescriberInterface $decorates,
        ) {
        }

        public function describe(Model $model, Schema $schema): void
        {
            $this->decorates->describe($model, $schema);

            /**
             * @var class-string<BackedEnum> $enumClass
             */
            $enumClass = $model->getType()->getClassName();

            $xEnumVarNames = [];
            foreach ($enumClass::cases() as $enumCase) {
                $xEnumVarNames[] = $enumCase->name;
            }

            $schema->x = [
                'enum-varnames' => $xEnumVarNames,
            ];
        }

        public function supports(Model $model): bool
        {
            return $this->decorates->supports($model);
        }
    }

Expected Output
~~~~~~~~~~~~~
With the above decorator, the generated schema for an enum
will include the ``x-enum-varnames`` extension:

.. configuration-block::

    .. code-block:: json

        {
            "components": {
                "schemas": {
                    "Status": {
                        "type": "string",
                        "enum": [
                            "active",
                            "inactive",
                            "pending"
                        ],
                        "x-enum-varnames": [
                            "ACTIVE",
                            "INACTIVE",
                            "PENDING"
                        ]
                    }
                }
            }
        }

    .. code-block:: yaml

        components:
            schemas:
                Status:
                    type: string
                    enum:
                        - active
                        - inactive
                        - pending
                    x-enum-varnames:
                        - ACTIVE
                        - INACTIVE
                        - PENDING

.. _ModelDescriberInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/ModelDescriber/ModelDescriberInterface.php
.. _SelfDescribingModelInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/ModelDescriber/SelfDescribingModelInterface.php