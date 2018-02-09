<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 *
 * ExampleExtension Class
 */
class ExampleExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('example', $options['example']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['example'] = $options['example'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['example' => null]);
    }

    public function getExtendedType()
    {
        return FormType::class;
    }
}
