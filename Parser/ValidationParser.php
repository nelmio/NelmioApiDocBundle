<?php

namespace Nelmio\ApiDocBundle\Parser;

use Nelmio\ApiDocBundle\Parser\ParserInterface;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

class ValidationParser implements ParserInterface
{
    /**
     * @var \Symfony\Component\Validator\MetadataFactoryInterface
     */
    protected $factory;

    public function __construct(MetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $input)
    {
        $className = $input['class'];

        return $this->factory->hasMetadataFor($className);
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input)
    {
        $params = array();
        $className = $input['class'];

        $classdata = $this->factory->getMetadataFor($className);
        $properties = $classdata->getConstrainedProperties();

        foreach($properties as $property) {
            $vparams = array();
            $pds = $classdata->getPropertyMetadata($property);
            foreach($pds as $propdata) {
                $constraints = $propdata->getConstraints();

                foreach($constraints as $constraint) {
                    $vparams = $this->parseConstraint($constraint, $vparams);
                }
            }

            if(isset($vparams['format'])) {
                $vparams['format'] = join(', ', $vparams['format']);
            }

            foreach(array('dataType', 'readonly', 'required') as $reqprop) {
                if(!isset($vparams[$reqprop])) {
                    $vparams[$reqprop] = null;
                }
            }

            $params[$property] = $vparams;
        }

        return $params;
    }

    public function postParse(array $input, $parameters)
    {
        foreach($parameters as $param => $data) {
            if(isset($data['class']) && isset($data['children'])) {
                $input = array('class' => $data['class']);
                $parameters[$param]['children'] = array_merge(
                    $parameters[$param]['children'], $this->postParse($input, $parameters[$param]['children'])
                );
                $parameters[$param]['children'] = array_merge(
                    $parameters[$param]['children'], $this->parse($input, $parameters[$param]['children'])
                );
            }
        }

        return $parameters;
    }

    protected function parseConstraint(Constraint $constraint, $vparams)
    {
        $class = substr(get_class($constraint), strlen('Symfony\\Component\\Validator\\Constraints\\'));

        switch($class) {
            case 'NotBlank':
            case 'NotNull':
                $vparams['required'] = true;
                break;
            case 'Type':
                $vparams['dataType'] = $constraint->type;
                break;
            case 'Email':
                $vparams['format'][] = '{email address}';
                break;
            case 'Url':
                $vparams['format'][] = '{url}';
                break;
            case 'Ip':
                $vparams['format'][] = '{ip address}';
                break;
            case 'Length':
                $messages = array();
                if(isset($constraint->min)) {
                    $messages[] = "min: {$constraint->min}";
                }
                if(isset($constraint->max)) {
                    $messages[] = "max: {$constraint->max}";
                }
                $vparams['format'][] = '{length: ' . join(', ', $messages) . '}';
                break;
            case 'Choice':
                $format = '[' . join('|', $constraint->choices) . ']';
                if($constraint->multiple) {
                    $messages = array();
                    if(isset($constraint->min)) {
                        $messages[] = "min: {$constraint->min} ";
                    }
                    if(isset($constraint->max)) {
                        $messages[] = "max: {$constraint->max} ";
                    }
                    $vparams['format'][] = '{' . join ('', $messages) . 'choice of ' . $format . '}';
                } else {
                    $vparams['format'][] = $format;
                }
                break;
            case 'Regex':
               if($constraint->match) {
                   $vparams['format'][] = '{match: ' . $constraint->pattern . '}';
               } else {
                   $vparams['format'][] = '{not match: ' . $constraint->pattern . '}';
               }
                break;
        }

        return $vparams;
    }
}