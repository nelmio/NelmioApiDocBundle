<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class DummyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bar', TextType::class, ['required' => false]);
        $builder->add('foo', ChoiceType::class, ['choices' => ['male', 'female']]);
        $builder->add('boo', ChoiceType::class, ['choices' => [true, false], 'required' => false]);
        $builder->add('foz', ChoiceType::class, ['choices' => ['male', 'female'], 'multiple' => true]);
        $builder->add('baz', CheckboxType::class, ['required' => false]);
        $builder->add('bey', IntegerType::class, ['required' => false]);
        $builder->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_name' => 'first_field',
            'required' => true,
        ]);
    }
}
