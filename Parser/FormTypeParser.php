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

use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

class FormTypeParser
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
     * Returns an array of data where each data is an array with the following keys:
     *  - dataType
     *  - required
     *  - description
     *
     * @param  string|\Symfony\Component\Form\FormTypeInterface $type
     * @return array
     */
    public function parse($type)
    {
        if (is_string($type) && class_exists($type)) {
            $type = new $type();
        }
        $form = $this->formFactory->create($type);

        $parameters = array();
        foreach ($form as $name => $child) {
            $config = $child->getConfig();

            $bestType = '';
            for ($type = $config->getType(); null !== $type; $type = $type->getParent()) {
                if (isset($this->mapTypes[$type->getName()])) {
                    $bestType = $this->mapTypes[$type->getName()];
                }
            }

            $parameters[$name] = array(
                'dataType'      => $bestType,
                'required'      => $config->getRequired(),
                'description'   => $config->getAttribute('description'),
            );
        }

        return $parameters;
    }
}
