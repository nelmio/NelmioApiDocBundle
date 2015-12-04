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
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $collectionType = 'Symfony\Component\Form\Extension\Core\Type\CollectionType';
        $builder
            ->add('a', LegacyFormHelper::getType($collectionType), array(
                LegacyFormHelper::hasBCBreaks() ? 'entry_type' : 'type' => LegacyFormHelper::getType('Symfony\Component\Form\Extension\Core\Type\TextType')
            ))
            ->add('b', LegacyFormHelper::getType($collectionType), array(
                LegacyFormHelper::hasBCBreaks() ? 'entry_type' : 'type' => LegacyFormHelper::isLegacy() ? new TestType() : __NAMESPACE__.'\TestType'
            ))
        ;
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
        return 'collection_type';
    }
}
