<?php
namespace Nelmio\ApiDocBundle\Tests\Functional\Form;

use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations\Definition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class DummyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('bar', TextType::class, ['required' => false]);
        $builder->add('foo', ChoiceType::class, ['choices' => ['male', 'female']]);
    }
}
