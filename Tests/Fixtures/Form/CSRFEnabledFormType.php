<?php
/**
 * User: Jonathan Chan <jchan@malwarebytes.org>
 * Date: 6/25/14
 * Time: 7:44 PM
 */


namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CSRFEnabledFormType extends AbstractType {


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('test1', 'text')
            ->add('test2', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'csrf_protection' => true,
            'csrf_provider' => new MockCSRFProvider(),
            'csrf_field_name' => '_token'
        ));
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return;
    }


}