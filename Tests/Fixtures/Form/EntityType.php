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

use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityType extends \Symfony\Bridge\Doctrine\Form\Type\EntityType implements DataMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('a', 'entity', array('multiple' => true));
        $builder->setDataMapper($this);
        $builder->setCompound(true);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple' => false,
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\EntityType'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function mapDataToForms($data, $forms)
    {

    }

    /**
     * {@inheritdoc}
     */
    public function mapFormsToData($forms, &$data)
    {

    }

    public function getName()
    {
        return 'entity_type';
    }
}
