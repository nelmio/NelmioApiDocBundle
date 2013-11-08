<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType as FormDateTimeType;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class DateTimeType implements FormTypeMapInterface
{
    public function findType(FormBuilderInterface $formBuilder)
    {
        return array("dataType"=>"datetime", "format"=>$formBuilder->getOption("format"));
    }
    public function supports(ResolvedFormTypeInterface $resolved)
    {
        return $resolved->getInnerType() instanceof FormDateTimeType;
    }
}
