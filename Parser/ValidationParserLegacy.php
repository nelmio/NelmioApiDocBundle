<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Parser;

use Symfony\Component\Validator\Mapping\ClassMetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Uses the Symfony Validation component to extract information about API objects. This is a backwards-compatible Validation component for Symfony2.1
 */
class ValidationParserLegacy implements ParserInterface, PostParserInterface
{
    /**
     * @var \Symfony\Component\Validator\MetadataFactoryInterface
     */
    protected $factory;

    /**
     * Requires a validation MetadataFactory.
     *
     * @param MetadataFactoryInterface $factory
     */
    public function __construct(ClassMetadataFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(array $input)
    {
        $className = $input['class'];       
        $metadata = $this->factory->getClassMetadata($className);
        return empty($metadata) ? false : true;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $input)
    {
        $params = array();
        $className = $input['class'];
        
        $classdata = $this->factory->getClassMetadata($className);
        
        $properties = $classdata->getConstrainedProperties();

        foreach($properties as $property) {
            $vparams = array();

            $pds = $classdata->getMemberMetadatas($property);            

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

    /**
     * {@inheritDoc}
     */
    public function postParse(array $input, array $parameters)
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

    /**
     * Create a valid documentation parameter based on an individual validation Constraint.
     * Currently supports:
     *  - NotBlank/NotNull
     *  - Type
     *  - Email
     *  - Url
     *  - Ip
     *  - Length (min and max)
     *  - Choice (single and multiple, min and max)
     *  - Regex (match and non-match)
     *
     * @param Constraint $constraint The constraint metadata object.
     * @param array $vparams         The existing validation parameters.
     * @return mixed                 The parsed list of validation parameters.
     */
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