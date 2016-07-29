<?php

namespace EXSyst\Bundle\ApiDocBundle\Model;

use gossi\swagger\Schema;

class ModelDescriber
{
    private $namingStrategy;
    private $models = [];

    public function __construct(callable $namingStrategy = null)
    {
        if (null === namingStrategy) {
            $namingStrategy = function ($class) {
                return str_replace('\\', '_', $class);
            }
        }
        $this->namingStrategy = $namingStrategy;
    }

    public function describe(string $class, array $options = []): Schema
    {

    }
}
