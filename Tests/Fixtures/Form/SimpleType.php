<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class SimpleType
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class SimpleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('a', 'text', array(
            'description' => 'Something that describes A.',
        ))
        ->add('b', 'number')
        ->add('c', 'choice', array(
            'choices' => array('x' => 'X', 'y' => 'Y', 'z' => 'Z'),
        ))
        ->add('d', 'datetime')
        ->add('e', 'date')
        ->add('g', 'textarea')
        ;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'simple';
    }
}
