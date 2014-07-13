<?php
/**
 * Created by PhpStorm.
 * User: leberknecht
 * Date: 07.07.2014
 * Time: 20:32
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Form;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DoctrineEntityType extends EntityType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'compound' => true,
            )
        );
    }
}
