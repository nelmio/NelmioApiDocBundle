<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

use Nelmio\ApiDocBundle\DataTypes;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class ApiModel
{
    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var string
     */
    protected $name;

    protected static $acceptableTypes = null;

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $name = null;
        if (count($parameters) === 1 && isset($parameters['value'])) {
            if (isset($parameters['value'][0]) && is_scalar($parameters['value'][0])) {
                $name = $parameters['value'][0];
                $parameters = $parameters['value'][1];
            }
        }
        $this->name = $name;
        $this->parameters = $this->normalizeParameters($parameters);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $parameters
     *
     * @throws \InvalidArgumentException
     */
    private function normalizeParameters($parameters)
    {
        foreach ($parameters as $name => $value) {
            if (!empty($value['type'])) {
                $types = self::getAcceptableTypes();
                if (!in_array($value['type'], array_keys($types))) {
                    throw new \InvalidArgumentException(sprintf('Unknown type "%s". Choose among: %s', $value['type'], json_encode(array_keys($types))));
                }
                $type = $types[$value['type']];
                unset($value['type']);
            } else {
                $type = DataTypes::STRING;
            }
            $value['actualType'] = $type;
            $parameters[$name] = $value;
        }

        return $parameters;
    }

    /**
     * @return array
     */
    protected static function getAcceptableTypes()
    {
        if (self::$acceptableTypes === null) {
            $reflClass = new \ReflectionClass('Nelmio\\ApiDocBundle\\DataTypes');
            foreach ($reflClass->getConstants() as $name => $value) {
                self::$acceptableTypes[strtolower($name)] = $value;
            }
        }

        return self::$acceptableTypes;
    }
}