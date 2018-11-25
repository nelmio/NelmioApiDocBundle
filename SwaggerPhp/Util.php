<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\SwaggerPhp;

use FOS\RestBundle\Controller\Annotations\Put;
use Swagger\Annotations\AbstractAnnotation;
use Swagger\Annotations\Definition;
use Swagger\Annotations\Delete;
use Swagger\Annotations\Get;
use Swagger\Annotations\Head;
use Swagger\Annotations\Header;
use Swagger\Annotations\Info;
use Swagger\Annotations\Items;
use Swagger\Annotations\Operation;
use Swagger\Annotations\Options;
use Swagger\Annotations\Parameter;
use Swagger\Annotations\Patch;
use Swagger\Annotations\Path;
use Swagger\Annotations\Post;
use Swagger\Annotations\Property;
use Swagger\Annotations\Response;
use Swagger\Annotations\Schema;
use Swagger\Annotations\Swagger;
use Swagger\Annotations\Tag;
use Swagger\Context;

/**
 * Class Util.
 *
 * This class acts as compatibility layer between NelmioApiDocBundle and swagger-php.
 *
 * It was written to replace the GuilhemN/swagger layer as a lower effort to maintain alternative.
 *
 * The main purpose of this class is to search for and create child Annotations
 * of swagger Annotation classes with the following convenience methods
 * to get or create the respective Annotation instances if not found
 *
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getPath()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getDefinition()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getSchem()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getProperty()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getOperation()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getOperationParameter()
 *
 * which in turn get or create the Annotation instances through the following more general methods
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getChild()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getCollectionItem()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::getIndexedCollectionItem()
 *
 * which then searches for an existing Annotation through
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::searchCollectionItem()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::searchIndexedCollectionItem()
 *
 * and if not found the Annotation creates it through
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::createCollectionItem()
 * @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::createContext()
 *
 * The merge method @see \Nelmio\ApiDocBundle\SwaggerPhp\Util::merge() has the main purpose to be able
 * to merge properties from an deeply nested array of Annotation properties in the structure of a
 * generated swagger json decoded array.
 */
class Util
{
    /**
     * All http method verbs as known by swagger.
     *
     * @var array
     */
    public static $operations = ['get', 'post', 'put', 'patch', 'delete', 'options', 'head'];

    /**
     * Return an existing Path object from $api->paths[] having its member path set to $path.
     * Create, add to $api->paths[] and return this new Path object and set the property if none found.
     *
     * @see \Swagger\Annotations\Swagger::$paths
     * @see \Swagger\Annotations\Path::path
     *
     * @param Swagger $api
     * @param string  $path
     *
     * @return Path
     */
    public static function getPath(Swagger $api, string $path): Path
    {
        return self::getIndexedCollectionItem($api, Path::class, $path);
    }

    /**
     * Return an existing Definition object from $api->definitions[] having its member definition set to $definition.
     * Create, add to $api->definitions[] and return this new Definition object and set the property if none found.
     *
     * @see \Swagger\Annotations\Swagger::$definitions
     * @see \Swagger\Annotations\Definition::$definition
     *
     * @param Swagger $api
     * @param string  $definition
     *
     * @return Definition
     */
    public static function getDefinition(Swagger $api, string $definition): Definition
    {
        return self::getIndexedCollectionItem($api, Definition::class, $definition);
    }

    /**
     * Return an existing Schema object from either a Response or a Parameter object $annotation->schema.
     * Create, set $annotation->schema and return this new Schema object if none exists.
     *
     * @see \Swagger\Annotations\Response::$schema
     * @see \Swagger\Annotations\Parameter::$schema
     *
     * @param Response|Parameter $annotation
     *
     * @return Schema
     */
    public static function getSchema(AbstractAnnotation $annotation): Schema
    {
        return self::getChild($annotation, Schema::class);
    }

    /**
     * Return an existing Property object from $schema->properties[]
     * having its member property set to $property.
     *
     * Create, add to $schema->properties[] and return this new Property object
     * and set the property if none found.
     *
     * @see \Swagger\Annotations\Schema::$properties
     * @see \Swagger\Annotations\Property::$property
     *
     * @param Schema $schema
     * @param string $property
     *
     * @return Property
     */
    public static function getProperty(Schema $schema, string $property): Property
    {
        return self::getIndexedCollectionItem($schema, Property::class, $property);
    }

    /**
     * Return an existing Operation from $path->{$method}
     * or create, set $path->{$method} and return this new Operation object.
     *
     * @see \Swagger\Annotations\Path::$get
     * @see \Swagger\Annotations\Path::$post
     * @see \Swagger\Annotations\Path::$put
     * @see \Swagger\Annotations\Path::$patch
     * @see \Swagger\Annotations\Path::$delete
     * @see \Swagger\Annotations\Path::$options
     * @see \Swagger\Annotations\Path::$head
     *
     * @param Path   $path
     * @param string $method
     *
     * @return Operation
     */
    public static function getOperation(Path $path, string $method): Operation
    {
        $class = array_keys($path::$_nested, \strtolower($method), true)[0];

        return self::getChild($path, $class, ['path' => $path->path]);
    }

    /**
     * Return an existing Parameter object from $operation->parameters[]
     * having its members name set to $name and in set to $in.
     *
     * Create, add to $operation->parameters[] and return
     * this new Parameter object and set its members if none found.
     *
     * @see \Swagger\Annotations\Operation::$parameters
     * @see \Swagger\Annotations\Parameter::$name
     * @see \Swagger\Annotations\Parameter::$in
     *
     * @param Operation $operation
     * @param string    $name
     * @param string    $in
     *
     * @return Parameter
     */
    public static function getOperationParameter(Operation $operation, string $name, string $in): Parameter
    {
        return self::getCollectionItem($operation, Parameter::class, ['name' => $name, 'in' => $in]);
    }

    /**
     * Return an existing nested Annotation from $parent->{$property} if exists.
     * Create, add to $parent->{$property} and set its members to $properties otherwise.
     *
     * $property is determined from $parent::$_nested[$class]
     * it is expected to be a string nested property.
     *
     * @see \Swagger\Annotations\AbstractAnnotation::$_nested
     *
     * @param AbstractAnnotation $parent
     * @param $class
     * @param array $properties
     *
     * @return AbstractAnnotation
     */
    public static function getChild(AbstractAnnotation $parent, $class, array $properties = []): AbstractAnnotation
    {
        $nested = $parent::$_nested;
        $property = $nested[$class];

        if (null === $parent->{$property}) {
            $parent->{$property} = self::createChild($parent, $class, $properties);
        }

        return $parent->{$property};
    }

    /**
     * Return an existing nested Annotation from $parent->{$collection}[]
     * having all $properties set to the respective values.
     *
     * Create, add to $parent->{$collection}[] and set its members
     * to $properties otherwise.
     *
     * $collection is determined from $parent::$_nested[$class]
     * it is expected to be a single value array nested Annotation.
     *
     * @see \Swagger\Annotations\AbstractAnnotation::$_nested
     *
     * @param AbstractAnnotation $parent
     * @param string             $class
     * @param array              $properties
     *
     * @return AbstractAnnotation
     */
    public static function getCollectionItem(AbstractAnnotation $parent, string $class, array $properties = []): AbstractAnnotation
    {
        $key = null;
        $nested = $parent::$_nested;
        $collection = $nested[$class][0];

        if (!empty($properties)) {
            $key = self::searchCollectionItem($parent->{$collection} ?: [], $properties);
        }
        if (null === $key) {
            $key = self::createCollectionItem($parent, $collection, $class, $properties);
        }

        return $parent->{$collection}[$key];
    }

    /**
     * Return an existing nested Annotation from $parent->{$collection}[]
     * having its mapped $property set to $value.
     *
     * Create, add to $parent->{$collection}[] and set its member $property to $value otherwise.
     *
     * $collection is determined from $parent::$_nested[$class]
     * it is expected to be a double value array nested Annotation
     * with the second value being the mapping index $property.
     *
     * @see \Swagger\Annotations\AbstractAnnotation::$_nested
     *
     * @param AbstractAnnotation $parent
     * @param string             $class
     * @param mixed              $value
     *
     * @return AbstractAnnotation
     */
    public static function getIndexedCollectionItem(AbstractAnnotation $parent, string $class, $value): AbstractAnnotation
    {
        $nested = $parent::$_nested;
        list($collection, $property) = $nested[$class];

        $key = self::searchIndexedCollectionItem($parent->{$collection} ?: [], $property, $value);

        if (false === $key) {
            $key = self::createCollectionItem($parent, $collection, $class, [$property => $value]);
        }

        return $parent->{$collection}[$key];
    }

    /**
     * Search for an Annotation within $collection that has all members set
     * to the respective values in the associative array $properties.
     *
     * @param array $collection
     * @param array $properties
     *
     * @return int|string|null
     */
    public static function searchCollectionItem(array $collection, array $properties)
    {
        foreach ($collection ?: [] as $i => $child) {
            foreach ($properties as $k => $prop) {
                if ($child->{$k} !== $prop) {
                    continue 2;
                }
            }

            return $i;
        }

        return null;
    }

    /**
     * Search for an Annotation within the $collection that has its member $index set to $value.
     *
     * @param array  $collection
     * @param string $member
     * @param mixed  $value
     *
     * @return false|int|string
     */
    public static function searchIndexedCollectionItem(array $collection, string $member, $value)
    {
        return array_search($value, array_column($collection, $member), true);
    }

    /**
     * Create a new Object of $class with members $properties within $parent->{$collection}[]
     * and return the created index.
     *
     * @param AbstractAnnotation $parent
     * @param string             $collection
     * @param string             $class
     * @param array              $properties
     *
     * @return int
     */
    public static function createCollectionItem(AbstractAnnotation $parent, string $collection, string $class, array $properties = []): int
    {
        $key = \count($parent->{$collection} ?: []);
        $parent->{$collection}[$key] = self::createChild($parent, $class, $properties);

        return $key;
    }

    /**
     * Create a new Object of $class with members $properties and set the context parent to be $parent.
     *
     *
     * @param AbstractAnnotation $parent
     * @param string             $class
     * @param array              $properties
     *
     * @throws \InvalidArgumentException at an attempt to pass in properties that are found in $parent::$_nested
     *
     * @return AbstractAnnotation
     */
    public static function createChild(AbstractAnnotation $parent, string $class, array $properties = []): AbstractAnnotation
    {
        $nesting = self::getNestingIndexes($class);

        if (!empty(array_intersect(array_keys($properties), $nesting))) {
            throw new \InvalidArgumentException('Nesting Annotations is not supported.');
        }

        return new $class(
            array_merge($properties, ['_context' => self::createContext(['nested' => $parent], $parent->_context)])
        );
    }

    /**
     * Create a new Context with members $properties and parent context $parent.
     *
     * @see \Swagger\Context
     *
     * @param array        $properties
     * @param Context|null $parent
     *
     * @return Context
     */
    public static function createContext(array $properties = [], ?Context $parent = null): Context
    {
        return new Context($properties, $parent);
    }

    /**
     * Merge $from into $annotation. $overwrite is only used for leaf scalar values.
     *
     * The main purpose is to create a Swagger Object from array config values
     * in the structure of a json serialized Swagger object.
     *
     * @param AbstractAnnotation                    $annotation
     * @param array|\ArrayObject|AbstractAnnotation $from
     * @param bool                                  $overwrite
     */
    public static function merge(AbstractAnnotation $annotation, $from, bool $overwrite = false): void
    {
        if (\is_array($from)) {
            self::mergeFromArray($annotation, $from, $overwrite);
        } elseif (\is_a($from, AbstractAnnotation::class)) {
            /* @var AbstractAnnotation $from */
            self::mergeFromArray($annotation, json_decode(json_encode($from), true), $overwrite);
        } elseif (\is_a($from, \ArrayObject::class)) {
            /* @var \ArrayObject $from */
            self::mergeFromArray($annotation, $from->getArrayCopy(), $overwrite);
        }
    }

    private static function mergeFromArray(AbstractAnnotation $annotation, array $properties, bool $overwrite): void
    {
        $done = [];

        foreach ($annotation::$_nested as $className => $propertyName) {
            if (\is_string($propertyName)) {
                if (array_key_exists($propertyName, $properties)) {
                    self::mergeChild($annotation, $className, $properties[$propertyName], $overwrite);
                    $done[] = $propertyName;
                }
            } elseif (\array_key_exists($propertyName[0], $properties)) {
                $collection = $propertyName[0];
                $property = $propertyName[1] ?? null;
                self::mergeCollection($annotation, $className, $collection, $property, $properties[$collection], $overwrite);
                $done[] = $collection;
            }
        }

        $defaults = \get_class_vars(\get_class($annotation));

        foreach ($annotation::$_types as $propertyName => $type) {
            if (array_key_exists($propertyName, $properties)) {
                self::mergeTyped($annotation, $propertyName, $type, $properties, $defaults, $overwrite);
                $done[] = $propertyName;
            }
        }

        foreach ($properties as $propertyName => $value) {
            if ('$ref' === $propertyName) {
                $propertyName = 'ref';
            }
            if (!\in_array($propertyName, $done, true)) {
                self::mergeProperty($annotation, $propertyName, $value, $defaults[$propertyName], $overwrite);
            }
        }
    }

    private static function mergeChild(AbstractAnnotation $annotation, $className, $value, bool $overwrite): void
    {
        self::merge(self::getChild($annotation, $className), $value, $overwrite);
    }

    private static function mergeCollection(AbstractAnnotation $annotation, $className, $collection, $property, $items, bool $overwrite): void
    {
        if (null !== $property) {
            foreach ($items as $prop => $value) {
                $child = self::getIndexedCollectionItem($annotation, $className, (string) $prop);
                self::merge($child, $value);
            }
        } else {
            $nesting = self::getNestingIndexes($className);
            foreach ($items as $props) {
                $create = [];
                $merge = [];
                foreach ($props as $k => $v) {
                    if (\in_array($k, $nesting, true)) {
                        $merge[$k] = $v;
                    } else {
                        $create[$k] = $v;
                    }
                }
                self::merge(self::getCollectionItem($annotation, $className, $create), $merge, $overwrite);
            }
        }
    }

    private static function mergeTyped(AbstractAnnotation $annotation, $propertyName, $type, array $properties, array $defaults, bool $overwrite): void
    {
        if (\is_string($type) && 0 === strpos($type, '[')) {
            /* type is declared as array in @see AbstractAnnotation::$_types */
            $annotation->{$propertyName} = array_unique(array_merge(
                $annotation->{$propertyName} ?: [],
                $properties[$propertyName]
            ));
        } else {
            self::mergeProperty($annotation, $propertyName, $properties[$propertyName], $defaults[$propertyName], $overwrite);
        }
    }

    private static function mergeProperty(AbstractAnnotation $annotation, $propertyName, $value, $default, bool $overwrite): void
    {
        if (true === $overwrite || $default === $annotation->{$propertyName}) {
            $annotation->{$propertyName} = $value;
        }
    }

    private static function getNestingIndexes($class): array
    {
        return array_values(array_map(
            function ($value) {
                return \is_array($value) ? $value[0] : $value;
            },
            self::getNesting($class) ?? []
        ));
    }

    private static function getNesting($class): ?array
    {
        switch ($class) {
            case Swagger::class:
                return Swagger::$_nested;
            case Info::class:
                return Info::$_nested;
            case Path::class:
                return Path::$_nested;
            case Get::class:
            case Post::class:
            case Put::class:
            case Delete::class:
            case Patch::class:
            case Head::class:
            case Options::class:
                return Operation::$_nested;
            case Parameter::class:
                return Parameter::$_nested;
            case Items::class:
                return Items::$_nested;
            case Property::class:
            case Definition::class:
                return Schema::$_nested;
            case Schema::class:
                return Schema::$_nested;
            case Tag::class:
                return Tag::$_nested;
            case Response::class:
                return Response::$_nested;
            case Header::class:
                return Header::$_nested;
            default:
                return null;
        }
    }
}
