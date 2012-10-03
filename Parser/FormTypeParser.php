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
        'date'      => 'date',
        'datetime'  => 'datetime',
        'checkbox'  => 'boolean',
        'time'      => 'time',
        'number'    => 'float',
        'integer'   => 'int',
        'textarea'  => 'string',
        'country'   => 'string',
    );

    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($item)
    {
        try {
            if (is_string($item) && class_exists($item)) {
                $item = unserialize(sprintf('O:%d:"%s":0:{}', strlen($item), $item));
            }

            $form = $this->formFactory->create($item);
        } catch (FormException $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($type)
    {
        if (is_string($type) && class_exists($type)) {
            $item = unserialize(sprintf('O:%d:"%s":0:{}', strlen($item), $item));
        }

        $form = $this->formFactory->create($type);

        return $this->parseForm($form);
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
            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                if (isset($this->mapTypes[$type->getName()])) {
                    $bestType = $this->mapTypes[$type->getName()];
                }
            }

            if ('' === $bestType) {
                if ($type = $config->getType()) {
                    if ($type = $type->getInnerType()) {
                        $subForm    = $this->formFactory->create($type);
                        $parameters = array_merge($parameters, $this->parseForm($subForm, $name));

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
        }

        return $parameters;
    }
}
