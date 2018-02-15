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
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * @internal
 */
final class FormModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;

    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory = null)
    {
        $this->formFactory = $formFactory;
    }

    public function describe(Model $model, Schema $schema)
    {
        if (method_exists(AbstractType::class, 'setDefaultOptions')) {
            throw new \LogicException('symfony/form < 3.0 is not supported, please upgrade to an higher version to use a form as a model.');
        }
        if (null === $this->formFactory) {
            throw new \LogicException('You need to enable forms in your application to use a form as a model.');
        }

        $schema->setType('object');

        $class = $model->getType()->getClassName();

        $form = $this->formFactory->create($class, null, []);
        $this->parseForm($schema, $form);
    }

    public function supports(Model $model): bool
    {
        return is_a($model->getType()->getClassName(), FormTypeInterface::class, true);
    }

    private function parseForm(Schema $schema, FormInterface $form)
    {
        $properties = $schema->getProperties();

        /** @var FormInterface $child */
        foreach ($form as $name => $child) {
            $config   = $child->getConfig();
            $property = $properties->get($name);
            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                $blockPrefix = $type->getBlockPrefix();
                $property->setType($this->getPropertyType(get_class($type->getInnerType()), $config));

                switch (true) {
                    case 'date' === $blockPrefix:
                        $property->setFormat('date');

                        break 2;
                    case 'datetime' === $blockPrefix:
                        $property->setFormat('date-time');

                        break 2;
                    case 'choice' === $blockPrefix:
                        if (($choices = $config->getOption('choices')) && is_array($choices) && count($choices)) {
                            $property->setEnum(array_values($choices));
                        }

                        break 2;
                    case 'collection' === $blockPrefix:
                        $subTypeClass = $config->getOption('entry_type');
                        $subType      = $this->getPropertyType($subTypeClass, $config);
                        if ('array' === $subType) {
                            $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $subType), null);
                            $property->getItems()->setRef($this->modelRegistry->register($model));
                        } else {
                            $property->getItems()->setType($subType);
                        }

                        $property->setExample(sprintf('[{%s}]', $subType));

                        break 2;
                    case 'entity' === $blockPrefix:
                        $entityClass = $config->getOption('class');
                        $property->setFormat(sprintf('%s id', $entityClass));

                        if ($config->getOption('multiple')) {
                            $property->setFormat(sprintf('[%s id]', $entityClass));
                            $property->setExample('[1, 2, 3]');
                        }

                        break;
                    case $type->getInnerType() && ($formClass = get_class(
                            $type->getInnerType()
                        )) && !$this->isBuiltinType($formClass):
                        //if form type is not builtin in Form component.
                        $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $formClass));
                        $property->setRef($this->modelRegistry->register($model));

                        break;
                    default:
                        break 2;
                }
            }

            foreach ($config->getOption('documentation', []) as $key => $value) {
                $method = 'set'.ucfirst($key);
                if (!method_exists($property, $method)) {
                    throw new \InvalidArgumentException('`' . $key . '`` is not a valid documentation property');
                }

                $property->{$method}($value);
            }

            if ($config->getRequired()) {
                $required = $schema->getRequired() ?? [];
                $required[] = $name;

                $schema->setRequired($required);
            }
        }
    }

    private function isBuiltinType(string $type): bool
    {
        return 0 === strpos($type, 'Symfony\Component\Form\Extension\Core\Type');
    }

    private function getPropertyType($class, FormConfigInterface $config)
    {
        switch ($class) {
            case NumberType::class:
                return 'number';
            case IntegerType::class:
                return 'integer';
            case CheckboxType::class:
                return 'boolean';
            case CollectionType::class:
                return 'array';
            case EntityType::class:
                return $config->getOption('multiple') ? 'array' : 'string';
            default:
                return 'string';
        }
    }
}
