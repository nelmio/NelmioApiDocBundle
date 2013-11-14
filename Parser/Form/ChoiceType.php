<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as ChoiceTypeForm;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class ChoiceType implements FormTypeMapInterface
{
    public function findType(FormBuilderInterface $formBuilder)
    {
        $choices =  $formBuilder->getOption("choices");
        if (is_array($choices) && is_scalar(key($choices))) {
            $type = gettype(key($choices));
        } else {
            $type= "choice";
        }

        return array("dataType"=>$type);
    }
    public function supports(ResolvedFormTypeInterface $resolved)
    {
        return $resolved->getInnerType() instanceof ChoiceTypeForm;
    }
}
