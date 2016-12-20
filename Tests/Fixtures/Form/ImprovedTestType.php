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

use Nelmio\ApiDocBundle\Util\LegacyFormHelper;
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
        $choiceType = LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\ChoiceType');
        $datetimeType = LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\DateTimeType');
        $dateType = LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\DateType');

        $builder
            ->add('dt1', $datetimeType, array('widget' => 'single_text', 'description' => 'A nice description'))
            ->add('dt2', $datetimeType, array('date_format' => 'M/d/y'))
            ->add('dt3', $datetimeType, array('widget' => 'single_text', 'format' => 'M/d/y H:i:s'))
            ->add('dt4', $datetimeType, array('date_format' => \IntlDateFormatter::MEDIUM))
            ->add('dt5', $datetimeType, array('format' => 'M/d/y H:i:s'))
            ->add('d1', $dateType, array('format' => \IntlDateFormatter::MEDIUM))
            ->add('d2', $dateType, array('format' => 'd-M-y'))
            ->add('c1', $choiceType, array_merge(
                array('choices' => array('m' => 'Male', 'f' => 'Female')), LegacyFormHelper::isLegacy() ? array() : array('choices_as_values' => true)
            ))
            ->add('c2', $choiceType, array_merge(
                array('choices' => array('m' => 'Male', 'f' => 'Female'), 'multiple' => true),
                LegacyFormHelper::isLegacy() ? array() : array('choices_as_values' => true)
            ))
            ->add('c3', $choiceType, array('choices' => array()))
            ->add('c4', $choiceType, array_merge(
                array('choices' => array('foo' => 'bar', 'bazgroup' => array('baz' => 'Buzz'))),
                LegacyFormHelper::isLegacy() ? array() : array('choices_as_values' => true)
            ))
            ->add('e1', LegacyFormHelper::isLegacy() ? new EntityType() : __NAMESPACE__.'\EntityType',
                LegacyFormHelper::isLegacy()
                    ? array('choice_list' => new SimpleChoiceList(array('foo' => 'bar', 'bazgroup' => array('baz' => 'Buzz'))))
                    : array('choices' => array('foo' => 'bar', 'bazgroup' => array('baz' => 'Buzz')), 'choices_as_values' => true)
            )
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

    /**
     * BC SF < 2.8
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
