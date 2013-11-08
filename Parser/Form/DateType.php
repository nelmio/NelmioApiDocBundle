<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType as FormDateType;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class DateType implements FormTypeMapInterface
{
    public function findType(FormBuilderInterface $formBuilder)
    {
        return array("dataType"=>"date", "format"=>$formBuilder->getOption("format"));
    }
    public function supports(ResolvedFormTypeInterface $resolved)
    {
        return $resolved->getInnerType() instanceof FormDateType;
    }
}
