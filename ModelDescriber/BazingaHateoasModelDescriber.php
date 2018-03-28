<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use EXSyst\Component\Swagger\Schema;
use Hateoas\Configuration\Relation;
use Hateoas\Serializer\Metadata\RelationPropertyMetadata;
use JMS\Serializer\Exclusion\GroupsExclusionStrategy;
use JMS\Serializer\SerializationContext;
use Metadata\MetadataFactoryInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\PropertyInfo\Type;

class BazingaHateoasModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $factory;

    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function describe(Model $model, Schema $schema)
    {
        $className = $model->getType()->getClassName();
        $metadata = $this->factory->getMetadataForClass($className);

        if (null === $metadata) {
            throw new \InvalidArgumentException(sprintf('No metadata found for class %s.', $className));
        }

        $groupsExclusion = null !== $model->getGroups() ? new GroupsExclusionStrategy($model->getGroups()) : null;

        $schema->setType('object');

        foreach ($metadata->getRelations() as $relation) {
            if (!$relation->getEmbedded() && !$relation->getHref()) {
                continue;
            }

            if (null !== $groupsExclusion && $relation->getExclusion()) {
                $item = new RelationPropertyMetadata($relation->getExclusion(), $relation);

                // filter groups
                if ($groupsExclusion->shouldSkipProperty($item, SerializationContext::create())) {
                    continue;
                }
            }

            $name = $relation->getName();

            $groups = $model->getGroups();
            if (isset($groups[$name]) && is_array($groups[$name])) {
                $groups = $model->getGroups()[$name];
            }

            if ($relation->getEmbedded()) {
                $embeddedSchema = $schema->getProperties()->get('_embedded');
                $properties = $embeddedSchema->getProperties();
            } else {
                $linksSchema = $schema->getProperties()->get('_links');
                $properties = $linksSchema->getProperties();
            }

            $property = $properties->get($name);
            $property->setType('object');

            if ($relation->getHref()) {
                $linkClassName = $this->generateLinkClass($className, $relation);
                $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $linkClassName), $groups);
                $property->setRef($this->modelRegistry->register($model));
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Model $model): bool
    {
        $className = $model->getType()->getClassName();

        try {
            if ($this->factory->getMetadataForClass($className)) {
                return true;
            }
        } catch (\ReflectionException $e) {
        }

        return false;
    }

    private function generateLinkClass($className, Relation $relation): string
    {
        $ref = new \ReflectionClass($className);

        $onlyClass = $ref->getShortName().'HateoasLink'.ucfirst($relation->getName());
        $onlyNs = $className;

        $classContent = "<?php\n";
        $classContent .= "namespace $onlyNs;\n";
        $classContent .= "class $onlyClass {\n";
        $classContent .= "
                    /** @Swagger\\Annotations\Property(type=\"string\") */
                    private \$href;\n";
        foreach ($relation->getAttributes() as $attribute => $value) {
            $classContent .= "
                    /** @Swagger\\Annotations\\Property(type=\"string\") */
                    private \$$attribute;\n";
        }
        $classContent .= '}';

        $linkClassName = "$onlyNs\\$onlyClass";

        if (!class_exists($linkClassName, false)) {
            $tempName = sys_get_temp_dir().DIRECTORY_SEPARATOR.md5($linkClassName);
            file_put_contents($tempName, $classContent);
            require_once $tempName;
        }

        return $linkClassName;
    }
}
