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
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormConfigBuilderInterface;
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

        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            $property = $properties->get($name);

            if ($config->getRequired()) {
                $required = $schema->getRequired() ?? [];
                $required[] = $name;

                $schema->setRequired($required);
            }

            $property->merge($config->getOption('documentation'));
            if (null !== $property->getType()) {
                continue; // Type manually defined
            }

            $this->findFormType($config, $property);
        }
    }

    /**
     * Finds and sets the schema type on $property based on $config info.
     *
     * Returns true if a native Swagger type was found, false otherwise
     *
     * @param FormConfigBuilderInterface $config
     * @param                            $property
     *
     * @return bool
     */
    private function findFormType(FormConfigBuilderInterface $config, $property): bool
    {
        for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
            $blockPrefix = $type->getBlockPrefix();

            if ('text' === $blockPrefix) {
                $property->setType('string');

                return true;
            }

            if ('number' === $blockPrefix) {
                $property->setType('number');

                return true;
            }

            if ('integer' === $blockPrefix) {
                $property->setType('integer');

                return true;
            }

            if ('date' === $blockPrefix) {
                $property->setType('string');
                $property->setFormat('date');

                return true;
            }

            if ('datetime' === $blockPrefix) {
                $property->setType('string');
                $property->setFormat('date-time');

                return true;
            }

            if ('choice' === $blockPrefix) {
                if ($config->getOption('multiple')) {
                    $property->setType('array');
                } else {
                    $property->setType('string');
                }
                if (($choices = $config->getOption('choices')) && is_array($choices) && count($choices)) {
                    $enums = array_values($choices);
                    $type = $this->isNumbersArray($enums) ? 'number' : 'string';
                    if ($config->getOption('multiple')) {
                        $property->getItems()->setType($type)->setEnum($enums);
                    } else {
                        $property->setType($type)->setEnum($enums);
                    }
                }

                return true;
            }

            if ('checkbox' === $blockPrefix) {
                $property->setType('boolean');
            }

            if ('collection' === $blockPrefix) {
                $subType = $config->getOption('entry_type');
                $subOptions = $config->getOption('entry_options');
                $subForm = $this->formFactory->create($subType, null, $subOptions);

                $property->setType('array');
                $itemsProp = $property->getItems();

                if (!$this->findFormType($subForm->getConfig(), $itemsProp)) {
                    $property->setExample(sprintf('[{%s}]', $subType));
                }

                return true;
            }

            if ('entity' === $blockPrefix) {
                $entityClass = $config->getOption('class');

                if ($config->getOption('multiple')) {
                    $property->setFormat(sprintf('[%s id]', $entityClass));
                    $property->setType('array');
                } else {
                    $property->setType('string');
                    $property->setFormat(sprintf('%s id', $entityClass));
                }

                return true;
            }

            if ($type->getInnerType() && ($formClass = get_class($type->getInnerType())) && !$this->isBuiltinType($formClass)) {
                // if form type is not builtin in Form component.
                $model = new Model(new Type(Type::BUILTIN_TYPE_OBJECT, false, $formClass));
                $property->setRef($this->modelRegistry->register($model));

                return false;
            }
        }

        return false;
    }

    /**
     * @param array $array
     *
     * @return bool true if $array contains only numbers, false otherwise
     */
    private function isNumbersArray(array $array): bool
    {
        foreach ($array as $item) {
            if (!is_numeric($item)) {
                return false;
            }
        }

        return true;
    }

    private function isBuiltinType(string $type): bool
    {
        return 0 === strpos($type, 'Symfony\Component\Form\Extension\Core\Type');
    }
}
