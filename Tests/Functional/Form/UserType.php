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

use Nelmio\ApiDocBundle\Tests\Functional\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('strings', CollectionType::class, [
                'entry_type' => TextType::class,
                'required' => false,
            ])
            ->add('dummy', DummyType::class)
            ->add('dummies', CollectionType::class, [
                'entry_type' => DummyType::class,
            ])
            ->add('empty_dummies', CollectionType::class, [
                'entry_type' => DummyEmptyType::class,
                'required' => false,
            ])
            ->add('quz', DummyType::class, ['documentation' => ['type' => 'string', 'description' => 'User type.'], 'required' => false])
            ->add('entity', EntityType::class, ['class' => 'Entity'])
            ->add('entities', EntityType::class, ['class' => 'Entity', 'multiple' => true])
            ->add('document', DocumentType::class, ['class' => 'Document'])
            ->add('documents', DocumentType::class, ['class' => 'Document', 'multiple' => true])
            ->add('extended_builtin', ExtendedBuiltinType::class, ['required_option' => 'foo'])
            ->add('save', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);

        $resolver->setRequired('bar');
    }
}
