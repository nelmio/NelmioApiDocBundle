<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 04.07.2014
 * Time: 21:07
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


    public function getName()
    {
        return 'entity_type';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'multiple' => false,
                'class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Form\EntityType'
            )
        );
    }

    public function mapDataToForms($data, $forms)
    {

    }

    public function mapFormsToData($forms, &$data){

    }
}
