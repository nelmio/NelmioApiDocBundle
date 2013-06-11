<?php

namespace Nelmio\ApiDocBundle\Parser\Handler;

use Nelmio\ApiDocBundle\Parser\HandlerInterface;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

class SymfonyValidationHandler implements HandlerInterface
{
    /**
     * @var \Symfony\Component\Validator\MetadataFactoryInterface
     */
    protected $factory;

    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    public function handle($className, $name, $params)
    {
        $vparams = array();

        $classdata = $this->factory->getMetadataFor($className);

        if($classdata->hasPropertyMetadata($name)) {
            $propdata = $classdata->getPropertyMetadata($name);
            $propdata = reset($propdata);
            $constraints = $propdata->getConstraints();

            foreach($constraints as $constraint) {
                $vparams = $this->parseConstraint($constraint, $vparams);
            }
        }

        return $vparams;
    }

    protected function parseConstraint(Constraint $constraint, $vparams)
    {
        $class = substr(get_class($constraint), strlen('Symfony\\Component\\Validator\\Constraints\\'));

        switch($class) {
            case 'NotBlank':
            case 'NotNull':
                $vparams['required'] = true;
                break;
        }

        return $vparams;
    }
}