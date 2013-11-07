<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
interface FormTypeMapInterface
{
    /**
     * Looks for a type.
     * If return an FormBuilderInterface, we are in a collection, else we have found a type
     * @param FormBuilderInterface $type
     * @return array|FormBuilderInterface
     */
    public function findType(FormBuilderInterface $builder);
    /**
     * Check if this mapper supports $builder from type
     * @param FormBuilderInterface $builder
     * @return boolean
     */
    public function supports(FormBuilderInterface $builder);
}
