Type (Property) Customization
=============================

The bundle uses a various property describers to generate the OpenAPI
schema for your properties. You can create your own property describer to
customize how your properties are represented in the documentation.

This is useful when you have custom value objects or when you want to represent
your properties in a different way than the default describers do.

.. note::

    Type describers are used for individual properties, while model describers
    are used for entire models (classes).

.. note::

    Type describers are chained together and executed in order of their priority.
    If multiple describers support the same property type, they will combine their
    generated schema.

.. important::

    When the ``type_info`` configuration option is set to ``true`` (which is recommended for Symfony 7.2+),
    the bundle uses `Symfony's TypeInfo component <https://symfony.com/doc/current/components/type_info.html>`_
    for type detection. In this scenario, you **must** implement `TypeDescriberInterface`_
    instead of the `PropertyDescriberInterface`_ for custom type descriptions.
    The `PropertyDescriberInterface`_  will **not** be used when ``type_info`` is enabled.

    The `TypeDescriberInterface`_ works similarly to the `PropertyDescriberInterface`_ but works with the
    ``Symfony\Component\TypeInfo\Type`` class.


For example, let's say you have a ``Currency`` value object that you
want to represent as a string (currency code) in your API documentation:

.. code-block:: php

    namespace App\ValueObject;

    class Currency
    {
        public string $code;
        public string $symbol;
    }

.. code-block:: php

    namespace App\Entity;

    use App\ValueObject\Currency;

    class Money
    {
        public int $cents;
        public Currency $currency;
    }

Creating a custom Type Describer `(type_info: true)`
----------------------------------------------------

To create a custom type describer, you need to create a class that implements the `TypeDescriberInterface`_.
This interface has two methods:

* ``supports(Type $type, array $context = []): bool``: This method should return ``true`` if your describer can handle the given property types.
* ``describe(Type $type, Schema $schema, array $context = [])``: This method should populate the OpenAPI ``Schema`` for the given property.

You can create a custom type describer for this ``Currency`` class like this:

.. code-block:: php

    namespace App\TypeDescriber;

    use App\ValueObject\Currency;
    use Nelmio\ApiDocBundle\PropertyDescriber\TypeDescriberInterface;
    use OpenApi\Annotations\Schema;
    use Symfony\Component\TypeInfo\Type;

    /**
     * @implements TypeDescriberInterface<ObjectType<Currency::class>>
     */
    class CurrencyTypeDescriber implements TypeDescriberInterface
    {
        public function describe(Type $type, Schema $property, array $context = []): void
        {
            $property->type = 'string';
            $property->example = 'USD';
            $property->description = 'A currency code represented as a string.';
        }

        public function supports(Type $type, array $context = []): bool
        {
            return $type instanceof Type\ObjectType
                && Currency::class === $type->getClassName();
        }
    }

Registering the custom Type Describer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are using Symfony's default ``services.yaml`` configuration, your custom
type describer will be automatically registered and tagged thanks to autoconfiguration!

If you're not using ``autoconfigure`` or if you need to set a priority to make sure your describer runs before or after
other describers, you can configure it manually in your ``services.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\TypeDescriber\CurrencyTypeDescriber:
                tags:
                    # register the type describer with a high priority (called earlier)
                    - { name: 'nelmio_api_doc.type_describer', priority: 100 }

    .. code-block:: php
        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\TypeDescriber\CurrencyTypeDescriber;

        return function(ContainerConfigurator $container) {
            // ...

            // if you're using autoconfigure, the tag will be automatically applied
            $services->set(App\TypeDescriber\CurrencyTypeDescriber::class)
                // register the type describer with a high priority (called earlier)
                ->tag('nelmio_api_doc.type_describer', [
                    'priority' => 100,
                ])
            ;
    };

Creating a custom Property Describer `(type_info: false)`
---------------------------------------------------------

To create a custom property describer, you need to create a class that implements the `PropertyDescriberInterface`_.
This interface has two methods:

* ``supports(array $types, array $context = []): bool``: This method should return ``true`` if your describer can handle the given property types.
* ``describe(array $types, Schema $property, array $context = []): void``: This method should populate the OpenAPI ``Schema`` for the given property.

You can create a custom property describer for this ``Currency`` class like this:

.. code-block:: php

    namespace App\PropertyDescriber;

    use App\ValueObject\Currency;
    use Nelmio\ApiDocBundle\PropertyDescriber\PropertyDescriberInterface;
    use OpenApi\Annotations\Schema;
    use Symfony\Component\PropertyInfo\Type;

    class CurrencyPropertyDescriber implements PropertyDescriberInterface
    {
        public function describe(array $types, Schema $property, array $context = []): void
        {
            $property->type = 'string';
            $property->example = 'USD';
            $property->description = 'A currency code represented as a string.';
        }

        public function supports(array $types, array $context = []): bool
        {
            if (1 !== \count($types)) {
                return false;
            }

            $type = $types[0];
            if (Type::BUILTIN_TYPE_OBJECT !== $type->getBuiltinType()) {
                return false;
            }

            return Currency::class === $type->getClassName();
        }
    }

Registering the custom Property Describer
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

If you are using Symfony's default ``services.yaml`` configuration, your custom
property describer will be automatically registered and tagged thanks to autoconfiguration!

If you're not using ``autoconfigure`` or if you need to set a priority to make sure your describer runs before or after
other describers, you can configure it manually in your ``services.yaml``:

.. configuration-block::

    .. code-block:: yaml

        # config/services.yaml
        services:
            # ...

            App\PropertyDescriber\CurrencyPropertyDescriber:
                tags:
                    # register the property describer with a high priority (called earlier)
                    - { name: 'nelmio_api_doc.property_describer', priority: 100 }

    .. code-block:: php

        // config/services.php
        namespace Symfony\Component\DependencyInjection\Loader\Configurator;

        use App\PropertyDescriber\CurrencyPropertyDescriber;

        return function(ContainerConfigurator $container) {
            // ...

            // if you're using autoconfigure, the tag will be automatically applied
            $services->set(App\PropertyDescriber\CurrencyPropertyDescriber::class)
                // register the property describer with a high priority (called earlier)
                ->tag('nelmio_api_doc.property_describer', [
                    'priority' => 100,
                ])
            ;
        };

Example Output
--------------
With the above describer examples, the generated ``components.schemas`` section
will include the following definition for the ``Money`` model:

.. configuration-block::

    .. code-block:: json

        {
            "components": {
                "schemas": {
                    "Money": {
                        "type": "object",
                        "properties": {
                            "cents": {
                                "type": "integer"
                            },
                            "currency": {
                                "type": "string",
                                "example": "USD",
                                "description": "A currency code represented as a string."
                            }
                    }
                }
            }
        }

    .. code-block:: yaml

        components:
            schemas:
                Money:
                    type: object
                    properties:
                        cents:
                            type: integer
                        currency:
                            type: string
                            example: "USD"
                            description: "A currency code represented as a string."

.. _PropertyDescriberInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/PropertyDescriber/PropertyDescriberInterface.php
.. _TypeDescriberInterface: https://github.com/nelmio/NelmioApiDocBundle/blob/5.x/src/TypeDescriber/TypeDescriberInterface.php
