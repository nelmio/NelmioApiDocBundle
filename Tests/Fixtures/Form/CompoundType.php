<?php
/**
 * Created by PhpStorm.
 * User: bezalelhermoso
 * Date: 6/24/14
 * Time: 1:03 PM
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


/**
 * Class CompoundType
 *
 * @package Nelmio\ApiDocBundle\Tests\Fixtures\Form
 * @author Bez Hermoso <bez@activelamp.com>
 */
class CompoundType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('sub_form', new SimpleType())
                ->add('a', 'number');
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return '';
    }
}