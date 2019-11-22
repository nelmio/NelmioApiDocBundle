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
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Aaron Scherer <aequasi@gmail.com>
 */
class DocumentationExtension extends AbstractTypeExtension
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('documentation', $options['documentation']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['documentation' => []])
            ->setAllowedTypes('documentation', ['array']);
    }

    public function getExtendedType()
    {
        return self::getExtendedTypes()[0];
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
