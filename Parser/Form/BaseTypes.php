<?php
namespace Nelmio\ApiDocBundle\Parser\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ResolvedFormTypeInterface;

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
        'url' => 'string',
        'email' => 'string',
        'telephone' => 'string',
        'hidden' => 'string',
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

    public function supports(ResolvedFormTypeInterface $resolved)
    {
        return isset($this->mapTypes[$resolved->getInnerType()->getName()]);
    }
}
