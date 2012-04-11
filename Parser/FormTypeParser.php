<?php

namespace Nelmio\ApiBundle\Parser;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormFactoryInterface;

class FormTypeParser
{
    protected $formFactory;

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

            $parameters[] = array(
                'name'          => $name,
                'type'          => $bestType,
                'is_required'   => $b->getRequired()
            );
        }

        return $parameters;
    }
}
