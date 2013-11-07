<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
/**
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 */
class BaseTypes implements FormTypeMapInterface
{

    /**
     *
     * @var array
     */
    protected $mapTypes = array(
        'text' => 'string',
        'checkbox' => 'boolean',
        'number' => 'float',
        'integer' => 'int',
        'textarea' => 'string',
        'country' => 'string'
    );

    public function findType(FormBuilderInterface $formBuilder)
    {
        return array(
            "dataType" => $this->mapTypes[$formBuilder->getType()->getName()],
        );
    }

    public function supports(FormBuilderInterface $formBuilder)
    {
        return isset($this->mapTypes[$formBuilder->getType()->getInnerType()->getName()]);
    }
}
