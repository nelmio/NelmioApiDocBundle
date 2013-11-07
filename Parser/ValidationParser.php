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

use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\MetadataFactoryInterface;
use Symfony\Component\Validator\Constraint;

/**
 * Uses the Symfony Validation component to extract information about API objects.
 */
class ValidationParser implements ParserInterface, PostParserInterface
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
        $className = $input['class'];
        return $this->doParse($className, array());
    }

    /**
     * Recursively parse constraints.
     *
     * @param  $className
     * @param  array $visited
     * @return array
     */
    protected function doParse ($className, array $visited)
    {
        $params = array();
        $classdata = $this->factory->getMetadataFor($className);
        $properties = $classdata->getConstrainedProperties();

        foreach($properties as $property) {
            $vparams = array();
            $pds = $classdata->getPropertyMetadata($property);
            foreach($pds as $propdata) {
                $constraints = $propdata->getConstraints();

                foreach($constraints as $constraint) {
                    $vparams = $this->parseConstraint($constraint, $vparams, $className);
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

            // check for nested classes with All constraint
            if (isset($vparams['class']) && !in_array($vparams['class'], $visited) && null !== $this->factory->getMetadataFor($vparams['class'])) {
                $visited[] = $vparams['class'];
                $vparams['children'] = $this->doParse($vparams['class'], $visited);
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
    protected function parseConstraint(Constraint $constraint, $vparams, $className)
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
                $choices = $this->getChoices($constraint, $className);
                $format = '[' . join('|', $choices) . ']';
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
            case 'All':
                foreach ($constraint->constraints as $childConstraint) {
                    if ($childConstraint instanceof Type) {
                        $nestedType = $childConstraint->type;
                        $exp = explode("\\", $nestedType);

                        $vparams['dataType'] = sprintf("array of objects (%s)", end($exp));
                        $vparams['class'] = $nestedType;
                    }
                }
                break;
        }

        return $vparams;
    }

    /**
     * Return Choice constraint choices.
     *
     * @param Constraint $constraint
     * @param $className
     * @return array
     * @throws \Symfony\Component\Validator\Exception\ConstraintDefinitionException
     */
    protected function getChoices (Constraint $constraint, $className)
    {
        if ($constraint->callback) {
            if (is_callable(array($className, $constraint->callback))) {
                $choices = call_user_func(array($className, $constraint->callback));
            } elseif (is_callable($constraint->callback)) {
                $choices = call_user_func($constraint->callback);
            } else {
                throw new ConstraintDefinitionException('The Choice constraint expects a valid callback');
            }
        } else {
            $choices = $constraint->choices;
        }

        return $choices;
    }
}
