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

use Nelmio\ApiDocBundle\DataTypes;

/**
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class FormErrorsParser implements ParserInterface, PostParserInterface
{
    /**
     * Return true/false whether this class supports parsing the given class.
     *
     * @param array $item containing the following fields: class, groups. Of which groups is optional
     *
     * @return boolean
     */
    public function supports(array $item)
    {
        return isset($item['form_errors']) && $item['form_errors'] === true;
    }

    public function parse(array $item)
    {
        return array();
    }

    /**
     * Overrides the root parameters to contain these parameters instead:
     *      - status_code: 400
     *      - message: "Validation failed"
     *      - errors: contains the original parameters, but all types are changed to array of strings (array of errors for each field)
     *
     * @param array $item
     * @param array $parameters
     *
     * @return array
     */
    public function postParse(array $item, array $parameters)
    {
        $params = $parameters;

        foreach ($params as $name => $data) {
            $params[$name] = null;
        }

        $params['status_code'] = array(
            'dataType' => 'integer',
            'actualType' => DataTypes::INTEGER,
            'subType' => null,
            'required' => false,
            'description' => 'The status code',
            'readonly' => true,
            'default' => 400,
        );

        $params['message'] = array(
            'dataType' => 'string',
            'actualType' => DataTypes::STRING,
            'subType' => null,
            'required' => false,
            'description' => 'The error message',
            'default' => 'Validation failed.',
        );

        $params['errors'] = array(
            'dataType' => 'errors',
            'actualType' => DataTypes::MODEL,
            'subType' => sprintf('%s.FormErrors', $item['class']),
            'required' => false,
            'description' => 'Errors',
            'readonly' => true,
            'children' => $this->doPostParse($parameters),
        );

        return $params;
    }

    protected function doPostParse(array $parameters, $attachFieldErrors = true, array $propertyPath = array())
    {
        $data = array();

        foreach ($parameters as $name => $parameter) {

            $data[$name] = array(
                'dataType' => 'parameter errors',
                'actualType' => DataTypes::MODEL,
                'subType' => 'FieldErrors',
                'required' => false,
                'description' => 'Errors on the parameter',
                'readonly' => true,
                'children' => array(
                    'errors' => array(
                        'dataType' => 'array of errors',
                        'actualType' => DataTypes::COLLECTION,
                        'subType' => 'string',
                        'required' => false,
                        'dscription' => '',
                        'readonly' => true,
                    ),
                ),
            );

            if ($parameter['actualType'] === DataTypes::MODEL) {
                $propertyPath[] = $name;
                $data[$name]['subType'] = sprintf('%s.FieldErrors[%s]', $parameter['subType'], implode('.', $propertyPath));
                $data[$name]['children'] = $this->doPostParse($parameter['children'], $attachFieldErrors, $propertyPath);
            } else {
                if ($attachFieldErrors === false) {
                    unset($data[$name]['children']);
                }
                $attachFieldErrors = false;
            }
        }

        return $data;
    }
}
