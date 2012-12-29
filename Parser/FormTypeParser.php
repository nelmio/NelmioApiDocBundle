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

use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Exception\FormException;

class FormTypeParser implements ParserInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var array
     */
    protected $mapTypes = array(
        'text'      => 'string',
        'date'      => 'DateTime',
        'datetime'  => 'DateTime',
        'checkbox'  => 'boolean',
        'time'      => 'DateTime',
        'number'    => 'double',
        'integer'   => 'integer',
        'textarea'  => 'string',
        'country'   => 'string',
    );

    public function __construct(FormFactoryInterface $formFactory, FormRegistry $formRegistry)
    {
        $this->formFactory  = $formFactory;
        $this->formRegistry = $formRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($item)
    {
        try {
            if ($this->createForm($item)) {
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
    public function parse($type)
    {
        if ($this->implementsType($type)) {
            $type = $this->getTypeInstance($type);
        }

        $form = $this->formFactory->create($type);

        return $this->parseForm($form);
    }

    private function parseForm($form)
    {
        $parameters = array();
        $children = array();
        foreach ($form as $name => $child) {
            $config = $child->getConfig();

            $bestType = '';
            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                if (isset($this->mapTypes[$type->getName()])) {
                    $bestType = $this->mapTypes[$type->getName()];
                }
            }

            if ('' === $bestType) {
                if ($type = $config->getType()) {

                    //TODO: collection?
                    if ('collection' === $config->getType()->getName()) {
                        $bestType = "array";
                    }

                    if ($type = $type->getInnerType()) {

                        /**
                         * TODO: Implement a better handling of unsupported types
                         * This is just a temporary workaround for don't breaking docs page in case of unsupported types
                         * like the entity type https://github.com/nelmio/NelmioApiDocBundle/issues/94
                         */
                        try {
                            $subForm    = $this->formFactory->create($type);
                            $parameters = array_merge($parameters, $this->parseForm($subForm, $name));
                        } catch (\Exception $e) {
                            $parameters[$name] = array(
                                'dataType'      => 'string',
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
                'required'      => $config->getRequired(),
                'description'   => $config->getAttribute('description'),
                'readonly'      => $config->getDisabled(),
            );

            if (!empty($children)) {
                $parameters[$name]['children'] = $children;
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
        if ($this->formRegistry->hasType($item)) {
            return $this->formFactory->create($item);
        }
    }
}
