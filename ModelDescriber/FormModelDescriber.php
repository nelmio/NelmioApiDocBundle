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

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareInterface;
use Nelmio\ApiDocBundle\Describer\ModelRegistryAwareTrait;
use Nelmio\ApiDocBundle\Model\Model;
use Nelmio\ApiDocBundle\ModelDescriber\Annotations\AnnotationsReader;
use Nelmio\ApiDocBundle\OpenApiPhp\ModelRegister;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Analysis;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;
use Symfony\Component\PropertyInfo\Type;

/**
 * @internal
 */
final class FormModelDescriber implements ModelDescriberInterface, ModelRegistryAwareInterface
{
    use ModelRegistryAwareTrait;
    use SetsContextTrait;

    private $formFactory;
    private $doctrineReader;
    private $mediaTypes;
    private $useValidationGroups;

    public function __construct(
        FormFactoryInterface $formFactory = null,
        Reader $reader = null,
        array $mediaTypes = null,
        bool $useValidationGroups = false
    ) {
        $this->formFactory = $formFactory;
        $this->doctrineReader = $reader;
        if (null === $reader) {
            @trigger_error(sprintf('Not passing a doctrine reader to the constructor of %s is deprecated since version 3.8 and won\'t be allowed in version 5.', self::class), E_USER_DEPRECATED);
        }

        if (null === $mediaTypes) {
            $mediaTypes = ['json'];
            @trigger_error(sprintf('Not passing media types to the constructor of %s is deprecated since version 4.1 and won\'t be allowed in version 5.', self::class), E_USER_DEPRECATED);
        }
        $this->mediaTypes = $mediaTypes;
        $this->useValidationGroups = $useValidationGroups;
    }

    public function describe(Model $model, OA\Schema $schema)
    {
        if (method_exists(AbstractType::class, 'setDefaultOptions')) {
            throw new \LogicException('symfony/form < 3.0 is not supported, please upgrade to an higher version to use a form as a model.');
        }
        if (null === $this->formFactory) {
            throw new \LogicException('You need to enable forms in your application to use a form as a model.');
        }

        $class = $model->getType()->getClassName();

        $annotationsReader = new AnnotationsReader(
            $this->doctrineReader,
            $this->modelRegistry,
            $this->mediaTypes,
            $this->useValidationGroups
        );
        $classResult = $annotationsReader->updateDefinition(new \ReflectionClass($class), $schema);

        if (!$classResult->shouldDescribeModelProperties()) {
            return;
        }

        $schema->type = 'object';

        $this->setContextFromReflection($schema->_context, new \ReflectionClass($class));

        $form = $this->formFactory->create($class, null, $model->getOptions() ?? []);
        $this->parseForm($schema, $form);

        $this->setContext(null);
    }

    public function supports(Model $model): bool
    {
        return is_a($model->getType()->getClassName(), FormTypeInterface::class, true);
    }

    private function parseForm(OA\Schema $schema, FormInterface $form)
    {
        foreach ($form as $name => $child) {
            $config = $child->getConfig();

            // This field must not be documented
            if ($config->hasOption('documentation') && false === $config->getOption('documentation')) {
                continue;
            }
            $property = Util::getProperty($schema, $name);

            if ($config->getRequired()) {
                $required = Generator::UNDEFINED !== $schema->required ? $schema->required : [];
                $required[] = $name;

                $schema->required = $required;
            }

            if ($config->hasOption('documentation')) {
                $property->mergeProperties($config->getOption('documentation'));

                // Parse inner @Model annotations
                $modelRegister = new ModelRegister($this->modelRegistry, $this->mediaTypes);
                $modelRegister->__invoke(new Analysis([$property], Util::createContext()));
            }

            if (Generator::UNDEFINED !== $property->type || Generator::UNDEFINED !== $property->ref) {
                continue; // Type manually defined
            }

            $this->findFormType($config, $property);
        }
    }

    /**
     * Finds and sets the schema type on $property based on $config info.
     *
     * Returns true if a native OpenAPi type was found, false otherwise
     */
    private function findFormType(FormConfigInterface $config, OA\Schema $property)
    {
        $type = $config->getType();

        if (!$builtinFormType = $this->getBuiltinFormType($type)) {
            // if form type is not builtin in Form component.
            $model = new Model(
                new Type(Type::BUILTIN_TYPE_OBJECT, false, get_class($type->getInnerType())),
                null,
                $config->getOptions()
            );

            $ref = $this->modelRegistry->register($model);
            // We need to use allOf for description and title to be displayed
            if ($config->hasOption('documentation') && !empty($config->getOption('documentation'))) {
                $property->allOf = [new OA\Schema(['ref' => $ref])];
            } else {
                $property->ref = $ref;
            }

            return;
        }

        do {
            $blockPrefix = $builtinFormType->getBlockPrefix();

            if ('text' === $blockPrefix) {
                $property->type = 'string';

                break;
            }

            if ('number' === $blockPrefix) {
                $property->type = 'number';

                break;
            }

            if ('integer' === $blockPrefix) {
                $property->type = 'integer';

                break;
            }

            if ('date' === $blockPrefix) {
                $property->type = 'string';
                $property->format = 'date';

                break;
            }

            if ('datetime' === $blockPrefix) {
                $property->type = 'string';
                $property->format = 'date-time';

                break;
            }

            if ('choice' === $blockPrefix) {
                if ($config->getOption('multiple')) {
                    $property->type = 'array';
                } else {
                    $property->type = 'string';
                }
                if (($choices = $config->getOption('choices')) && is_array($choices) && count($choices)) {
                    $enums = array_values($choices);
                    if ($this->isNumbersArray($enums)) {
                        $type = 'number';
                    } elseif ($this->isBooleansArray($enums)) {
                        $type = 'boolean';
                    } else {
                        $type = 'string';
                    }

                    if ($config->getOption('multiple')) {
                        $property->items = Util::createChild($property, OA\Items::class, ['type' => $type, 'enum' => $enums]);
                    } else {
                        $property->type = $type;
                        $property->enum = $enums;
                    }
                }

                break;
            }

            if ('checkbox' === $blockPrefix) {
                $property->type= 'boolean';

                break;
            }

            if ('password' === $blockPrefix) {
                $property->type = 'string';
                $property->format = 'password';

                break;
            }

            if ('repeated' === $blockPrefix) {
                $property->type = 'object';
                $property->required = [$config->getOption('first_name'), $config->getOption('second_name')];
                $subType = $config->getOption('type');

                foreach (['first', 'second'] as $subField) {
                    $subName = $config->getOption($subField.'_name');
                    $subForm = $this->formFactory->create($subType, null, array_merge($config->getOption('options'), $config->getOption($subField.'_options')));

                    $this->findFormType($subForm->getConfig(), Util::getProperty($property, $subName));
                }

                break;
            }

            if ('collection' === $blockPrefix) {
                $subType = $config->getOption('entry_type');
                $subOptions = $config->getOption('entry_options');
                $subForm = $this->formFactory->create($subType, null, $subOptions);

                $property->type = 'array';
                $property->items = Util::createChild($property, OA\Items::class);

                $this->findFormType($subForm->getConfig(), $property->items);

                break;
            }

            // The DocumentType is bundled with the DoctrineMongoDBBundle
            if ('entity' === $blockPrefix || 'document' === $blockPrefix) {
                $entityClass = $config->getOption('class');

                if ($config->getOption('multiple')) {
                    $property->format = sprintf('[%s id]', $entityClass);
                    $property->type = 'array';
                    $property->items = Util::createChild($property, OA\Items::class, ['type' => 'string']);
                } else {
                    $property->type = 'string';
                    $property->format = sprintf('%s id', $entityClass);
                }

                break;
            }
        } while ($builtinFormType = $builtinFormType->getParent());
    }

    /**
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

    /**
     * @return bool true if $array contains only booleans, false otherwise
     */
    private function isBooleansArray(array $array): bool
    {
        foreach ($array as $item) {
            if (!is_bool($item)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return ResolvedFormTypeInterface|null
     */
    private function getBuiltinFormType(ResolvedFormTypeInterface $type)
    {
        do {
            $class = get_class($type->getInnerType());

            if (FormType::class === $class) {
                return null;
            }

            if ('entity' === $type->getBlockPrefix() || 'document' === $type->getBlockPrefix()) {
                return $type;
            }

            if (0 === strpos($class, 'Symfony\Component\Form\Extension\Core\Type\\')) {
                return $type;
            }
        } while ($type = $type->getParent());

        return null;
    }
}
