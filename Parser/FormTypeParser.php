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

use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Form\Exception\InvalidArgumentException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Exception\FormException;
use Symfony\Component\Form\FormBuilderInterface;
use Nelmio\ApiDocBundle\Parser\Form\FormTypeMapInterface;
use Symfony\Component\Form\FormTypeInterface;

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
    protected $mapTypes = array();

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory  = $formFactory;
    }
    /**
     *
     * @param  FormTypeMapInterface $mapper
     * @return \Nelmio\ApiDocBundle\Parser\FormTypeParser
     */
    public function addTypeMapper(FormTypeMapInterface $mapper)
    {
        $this->mapTypes[] = $mapper;
        return $this;
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
    private function findType(FormBuilderInterface $config, $name)
    {
        $params = array();
        $params[$name] = array(
            'dataType'      => 'unknown',
            'required'      => $config->getRequired(),
            'description'   => $config->getAttribute('description'),
            'readonly'      => $config->getDisabled(),
        );

        for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
            $innerType = $type->getInnerType();
            foreach ($this->mapTypes as $typeMapper) {
                if ($typeMapper->supports($config)) {

                    $definition = $typeMapper->findType($config);
                    if (is_array($definition)) {
                        $params[$name] = array_merge(array(
                            'required'      => $config->getRequired(),
                            'description'   => $config->getAttribute('description'),
                            'readonly'      => $config->getDisabled(),
                        ), array_filter($definition));

                        return $params;
                    } else { // in a collection
                        unset($params[$name]);

                        $subParams = $this->parseForm($definition, "{$name}[ ]");

                        if (!$subParams) {

                            $subsubParams = $this->findType($definition->getConfig(), $config->getName());

                            $subParams["{$name}[ ]"]=array(
                                'dataType'      => $subsubParams[$config->getName()][dataType]?:'unknown',
                                'required'      => $config->getRequired(),
                                'description'   => $config->getAttribute('description'),
                                'readonly'      => $config->getDisabled(),
                            );
                        }
                        $params = array_merge($params, $subParams);
                    }

                }
            }
        }

        if (($type = $config->getType()) && $type = $type->getInnerType()) {
            try {
                $subForm = $this->formFactory->create($type);
                $subParams = $this->parseForm($subForm, $name);
                if ($subParams) {
                    unset($params[$name]);
                }
                $params = array_merge($params, $subParams);
            } catch (\Exception $e) {

            }
        }

        return $params;
    }
    private function parseForm($form, $prefix=null)
    {
        $parameters = array();
        foreach ($form as $name => $child) {
            $config = $child->getConfig();
            if ($prefix) {
                $name = sprintf('%s[%s]', $prefix, $name);
            }
            $parameters = array_merge($parameters, $this->findType($config, $name));
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
}
