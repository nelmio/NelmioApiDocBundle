<?php
namespace Nelmio\ApiDocBundle\Parser\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType as FormCollectionType;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Symfony\Component\Form\FormTypeInterface;
/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class CollectionType implements FormTypeMapInterface
{
    protected $formTypeParser;
    protected $formFactory;

    public function __construct(FormTypeParser $formTypeParser, $formFactory)
    {
        $this->formTypeParser = $formTypeParser;
        $this->formFactory = $formFactory;
    }
    public function findType(FormBuilderInterface $formBuilder)
    {

        $type = $formBuilder->getOption('type');
        if (!($type instanceof FormTypeInterface) && $this->implementsType($type)) {
            $type = $this->getTypeInstance($type);
        }

        $form = $this->formFactory->create($type, null, $formBuilder->getOption('options'));

        return $form;
    }
    public function supports(FormBuilderInterface $formBuilder)
    {
        return $formBuilder->getType()->getInnerType() instanceof FormCollectionType;
    }

    private function implementsType($item)
    {
        if (!class_exists($item)) {
            return false;
        }
        $refl = new \ReflectionClass($item);

        return $refl->implementsInterface('Symfony\Component\Form\FormBuilderInterface');
    }

    private function getTypeInstance($type)
    {
        return unserialize(sprintf('O:%d:"%s":0:{}', strlen($type), $type));
    }
}
