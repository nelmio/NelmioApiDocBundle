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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RequireConstructionType extends AbstractType
{
    private $noThrow;

    public function __construct($optionalArgs = null)
    {
        $this->noThrow = true;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->noThrow !== true)
            throw new \RuntimeException(__CLASS__ . " require contruction");

        $builder
            ->add('a', null, array('description' => 'A nice description'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\Test',
        ));

        return;
    }

    public function getName()
    {
        return 'require_construction_type';
    }
}
