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

use Symfony\Component\Form\AbstractType;
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
     * @param AbstractType $type
     * @return array
     */
    public function parse(AbstractType $type)
    {
        $builder = $this->formFactory->createBuilder($type);

        $parameters = array();
        foreach ($builder->all() as $name => $child) {
            $b = $builder->create($name, $child['type'], $child['options']);

            $bestType = '';
            foreach ($b->getTypes() as $type) {
                if (isset($this->mapTypes[$type->getName()])) {
                    $bestType = $this->mapTypes[$type->getName()];
                }
            }

            $parameters[$name] = array(
                'dataType'      => $bestType,
                'required'      => $b->getRequired(),
                'description'   => $b->getAttribute('description'),
            );
        }

        return $parameters;
    }
}
