<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\ModelDescriber\Annotations;

/**
 * result object returned from `AnnotationReader::updateDefinition` as a way
 * to pass back information about manually defined schema elements.
 *
 * @internal
 */
final class UpdateClassDefinitionResult
{
    /**
     * Whether the model describer should continue reading class properties
     * after updating the open api schema from an `OA\Schema` definition.
     *
     * Users may manually define a `type` or `ref` on a schema, and if that's the case
     * model describers should _probably_ not describe any additional properties or try
     * to merge in properties.
     */
    private bool $shouldDescribeModelProperties;

    public function __construct(bool $shouldDescribeModelProperties)
    {
        $this->shouldDescribeModelProperties = $shouldDescribeModelProperties;
    }

    public function shouldDescribeModelProperties(): bool
    {
        return $this->shouldDescribeModelProperties;
    }
}
