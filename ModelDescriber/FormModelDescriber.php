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
use Nelmio\ApiDocBundle\Model\Model;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;

/**
 * @internal
 */
final class FormModelDescriber implements ModelDescriberInterface
{
    private $formFactory;

    public function __construct(FormFactoryInterface $formFactory = null)
    {
        $this->formFactory = $formFactory;
    }

    public function describe(Model $model, Schema $schema)
    {
        if (method_exists('Symfony\Component\Form\AbstractType', 'setDefaultOptions')) {
            throw new \LogicException('symfony/form < 3.0 is not supported, please upgrade to an higher version to use a form as a model.');
        }
        if (null === $this->formFactory) {
            throw new \LogicException('You need to enable forms in your application to use a form as a model.');
        }

        $schema->setType('object');
        $properties = $schema->getProperties();

        $class = $model->getType()->getClassName();

        $form = $this->formFactory->create($class, null, []);
        $this->parseForm($schema, $form);
    }

    public function supports(Model $model): bool
    {
        return is_a($model->getType()->getClassName(), FormTypeInterface::class, true);
    }

    private function parseForm(Schema $schema, $form)
    {
        $properties = $schema->getProperties();
        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            $property = $properties->get($name);
            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                $blockPrefix = $type->getBlockPrefix();

                if ('text' === $blockPrefix) {
                    $property->setType('string');
                    break;
                }
                if ('date' === $blockPrefix) {
                    $property->setType('string');
                    $property->setFormat('date');
                    break;
                }
                if ('datetime' === $blockPrefix) {
                    $property->setType('string');
                    $property->setFormat('date-time');
                    break;
                }
                if ('choice' === $blockPrefix) {
                    $property->setType('string');
                    if (($choices = $config->getOption('choices')) && is_array($choices) && count($choices)) {
                        $property->setEnum(array_values($choices));
                    }

                    break;
                }
                if ('collection' === $blockPrefix) {
                    $subType = $config->getOption('entry_type');
                }
            }

            if ($config->getRequired()) {
                $required = $schema->getRequired() ?? [];
                $required[] = $name;

                $schema->setRequired($required);
            }
        }
    }
}
