<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Parser;

use Nelmio\ApiDocBundle\DataTypes;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Exception\FormException;

class FormTypeParser implements ParserInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var \Symfony\Component\Form\FormRegistry
     */
    protected $formRegistry;

    /**
     * @var array
     */
    protected $mapTypes = array(
        'text'      => DataTypes::STRING,
        'date'      => DataTypes::DATE,
        'datetime'  => DataTypes::DATETIME,
        'checkbox'  => DataTypes::BOOLEAN,
        'time'      => DataTypes::TIME,
        'number'    => DataTypes::FLOAT,
        'integer'   => DataTypes::INTEGER,
        'textarea'  => DataTypes::STRING,
        'country'   => DataTypes::STRING,
        'choice'    => DataTypes::ENUM,
        'file'      => DataTypes::FILE,
    );

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory  = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        $className = $item['class'];

        try {
            if ($this->createForm($className)) {
                return true;
            }
        } catch (FormException $e) {
            return false;
        } catch (MissingOptionsException $e) {
            return false;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $item)
    {
        $type = $item['class'];

        if ($this->implementsType($type)) {
            $type = $this->getTypeInstance($type);
        }

        $form = $this->formFactory->create($type);

        return $this->parseForm($form, array_key_exists('name', $item) ? $item['name'] : $form->getName());
    }

    private function parseForm($form, $prefix = null)
    {
        $parameters = array();
        foreach ($form as $name => $child) {
            $config = $child->getConfig();


            if ($prefix) {
                $name = sprintf('%s[%s]', $prefix, $name);
            }

            $bestType = '';
            $actualType = null;
            $subType = null;

            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                if (isset($this->mapTypes[$type->getName()])) {
                    $bestType = $this->mapTypes[$type->getName()];
                    $actualType = $bestType;
                } elseif ('collection' === $type->getName()) {
                    if (is_string($config->getOption('type')) && isset($this->mapTypes[$config->getOption('type')])) {
                        $subType = $this->mapTypes[$config->getOption('type')];
                        $actualType = DataTypes::COLLECTION;
                        $bestType = sprintf('array of %ss', $subType);
                    } else {
                        // Embedded form collection
                        $subParameters = $this->parseForm($this->formFactory->create($config->getOption('type'), null, $config->getOption('options', array())), $name . '[]');
                        $parameters = array_merge($parameters, $subParameters);

                        continue 2;
                    }
                }
            }

            if ('' === $bestType) {
                if ($type = $config->getType()) {
                    if ($type = $type->getInnerType()) {
                        /**
                         * TODO: Implement a better handling of unsupported types
                         * This is just a temporary workaround for don't breaking docs page in case of unsupported types
                         * like the entity type https://github.com/nelmio/NelmioApiDocBundle/issues/94
                         */
                        $addDefault = false;
                        try {
                            $subForm    = $this->formFactory->create($type);
                            $subParameters = $this->parseForm($subForm, $name);
                            if (!empty($subParameters)) {
                                $parameters = array_merge($parameters, $subParameters);
                            } else {
                                $addDefault = true;
                            }
                        } catch (\Exception $e) {
                            $addDefault = true;
                        }

                        if ($addDefault) {
                            $parameters[$name] = array(
                                'dataType'      => 'string',
                                'default'       => $config->getData(),
                                'actualType'    => 'string',
                                'required'      => $config->getRequired(),
                                'description'   => $config->getAttribute('description'),
                                'readonly'      => $config->getDisabled(),
                            );
                        }

                        continue;
                    }
                }
            }

            $parameters[$name] = array(
                'dataType'      => $bestType,
                'default'       => $config->getData(),
                'actualType'    => $actualType,
                'subType'       => $subType,
                'required'      => $config->getRequired(),
                'description'   => $config->getAttribute('description'),
                'readonly'      => $config->getDisabled(),
            );

            switch ($actualType) {
                case DataTypes::DATETIME:
                    if (($format = $config->getOption('date_format')) && is_string($format)) {
                        $parameters[$name]['format'] = $format;
                    } elseif ('single_text' == $config->getOption('widget') && $format = $config->getOption('format')) {
                        $parameters[$name]['format'] = $format;
                    }
                    break;

                case DataTypes::DATE:
                    if (($format = $config->getOption('format')) && is_string($format)) {
                        $parameters[$name]['format'] = $format;
                    }
                    break;

                case DataTypes::ENUM:
                    if ($config->getOption('multiple')) {
                        $parameters[$name]['dataType'] = sprintf('array of %ss', $parameters[$name]['dataType']);
                        $parameters[$name]['actualType'] = DataTypes::COLLECTION;
                        $parameters[$name]['subType'] = DataTypes::ENUM;
                    }

                    if (($choices = $config->getOption('choices')) && is_array($choices) && count($choices)) {
                        $parameters[$name]['format'] = json_encode($choices);
                    } elseif (($choiceList = $config->getOption('choice_list')) && $choiceList instanceof ChoiceListInterface) {
                        $choices = $this->handleChoiceListValues($choiceList);
                        if (is_array($choices) && count($choices)) {
                            $parameters[$name]['format'] = json_encode($choices);
                        }
                    }
                    break;
            }
        }

        return $parameters;
    }

    private function implementsType($item)
    {
        if (!class_exists($item)) {
            return false;
        }
        $refl = new \ReflectionClass($item);

        return $refl->implementsInterface('Symfony\Component\Form\FormTypeInterface');
    }

    private function getTypeInstance($type)
    {
        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($type), $type));
    }

    private function createForm($item)
    {
        if ($this->implementsType($item)) {
            $type = $this->getTypeInstance($item);

            return $this->formFactory->create($type);
        }
        try {
            return $this->formFactory->create($item);
        } catch (UnexpectedTypeException $e) {
            // nothing
        } catch (InvalidArgumentException $e) {
            // nothing
        }
    }

    private function handleChoiceListValues(ChoiceListInterface $choiceList)
    {
        $choices = array();
        foreach (array($choiceList->getPreferredViews(), $choiceList->getRemainingViews()) as $viewList) {
            $choices = array_merge($choices, $this->handleChoiceViewsHierarchy($viewList));
        }

        return $choices;
    }

    private function handleChoiceViewsHierarchy(array $choiceViews)
    {
        $choices = array();
        foreach ($choiceViews as $item) {
            if ($item instanceof ChoiceView) {
                $choices[$item->value] = $item->label;
            } elseif (is_array($item)) {
                $choices = array_merge($choices, $this->handleChoiceViewsHierarchy($item));
            }
        }

        return $choices;
    }
}
