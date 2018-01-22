<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber;

use Doctrine\Common\Annotations\Reader;
use EXSyst\Component\Swagger\Schema;
use Swagger\Annotations\Definition as SwgDefinition;

/**
 * @internal
 */
class SwaggerDefinitionAnnotationReader
{
    private $annotationsReader;

    public function __construct(Reader $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    public function updateWithSwaggerDefinitionAnnotation(\ReflectionClass $reflectionClass, Schema $schema)
    {
        /** @var SwgDefinition $swgDefinition */
        if (!$swgDefinition = $this->annotationsReader->getClassAnnotation($reflectionClass, SwgDefinition::class)) {
            return;
        }

        if (null !== $swgDefinition->required) {
            $schema->setRequired($swgDefinition->required);
        }
    }
}
