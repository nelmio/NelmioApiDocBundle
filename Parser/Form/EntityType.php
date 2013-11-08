<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\Form\ResolvedFormTypeInterface;

/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class EntityType implements FormTypeMapInterface
{
    /**
     *
     * @var \Symfony\Bridge\Doctrine\RegistryInterface
     */
    protected $registry;
    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }
    public function findType(FormBuilderInterface $formBuilder)
    {
        $class = $formBuilder->getOption("class");
        $em = $this->registry->getManagerForClass($class);
        $metadata = $em->getClassMetadata($class);
        foreach ($metadata->identifier as $idName) {
            return array("dataType"=>$metadata->fieldMappings[$idName]["type"]);
        }

    }
    public function supports(ResolvedFormTypeInterface $resolved)
    {
        return $resolved->getInnerType() instanceof DoctrineType;
    }
}
