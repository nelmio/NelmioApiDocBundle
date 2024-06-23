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
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->setAttribute('documentation', $options['documentation']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['documentation' => []])
            ->setAllowedTypes('documentation', ['array', 'bool']);
    }

    /**
     * @deprecated since Symfony 4.2, use getExtendedTypes() instead.
     *
     * @return string
     */
    public function getExtendedType()
    {
        trigger_deprecation(
            'nelmio/api-doc-bundle',
            '4.28.1',
            'Calling %s is deprecated since Symfony 4.2, call %s instead',
            __METHOD__,
            'DocumentationExtension::getExtendedTypes()',
        );

        return self::getExtendedTypes()[0];
    }

    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
