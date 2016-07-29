<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Describer;

class SwaggerPhpDescriber extends ExternalDocDescriber
{
    /**
     * @param string $projectPath
     */
    public function __construct(string $projectPath, bool $overwrite = false)
    {
        parent::__construct(function () use ($projectPath) {
            $annotation = \Swagger\scan($projectPath);

            return json_decode(json_encode($annotation));
        }, $overwrite);
    }
}
