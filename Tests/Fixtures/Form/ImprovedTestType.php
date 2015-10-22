<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ImprovedTestType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dt1', 'datetime', array('widget' => 'single_text', 'description' => 'A nice description'))
            ->add('dt2', 'datetime', array('date_format' => 'M/d/y'))
            ->add('dt3', 'datetime', array('widget' => 'single_text', 'format' => 'M/d/y H:i:s'))
            ->add('dt4', 'datetime', array('date_format' => \IntlDateFormatter::MEDIUM))
            ->add('dt5', 'datetime', array('format' => 'M/d/y H:i:s'))
            ->add('d1', 'date', array('format' => \IntlDateFormatter::MEDIUM))
            ->add('d2', 'date', array('format' => 'd-M-y'))
            ->add('c1', 'choice', array('choices' => array('m' => 'Male', 'f' => 'Female')))
            ->add('c2', 'choice', array('choices' => array('m' => 'Male', 'f' => 'Female'), 'multiple' => true))
            ->add('c3', 'choice', array('choices' => array()))
            ->add('c4', 'choice', array('choices' => array('foo' => 'bar', 'bazgroup' => array('baz' => 'Buzz'))))
            ->add('e1', new EntityType(), array('choice_list' => new SimpleChoiceList(array('foo' => 'bar', 'bazgroup' => array('baz' => 'Buzz')))))
        ;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Remove it when bumping requirements to Symfony 2.7+
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Nelmio\ApiDocBundle\Tests\Fixtures\Model\ImprovedTest',
        ));

        return;
    }

    public function getName()
    {
        return '';
    }
}
