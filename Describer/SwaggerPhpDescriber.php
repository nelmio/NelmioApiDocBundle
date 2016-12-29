<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

final class SwaggerPhpDescriber extends ExternalDocDescriber
{
    public function __construct(string $projectPath, bool $overwrite = false)
    {
        parent::__construct(function () use ($projectPath) {
            // Catch notices as the documentation can be completed by other describers
            $prevHandler = set_error_handler(function ($type, $message, $file, $line, $context) use (&$prevHandler) {
                if (E_USER_NOTICE === $type || E_USER_WARNING === $type) {
                    return;
                }

                return null !== $prevHandler && call_user_func($prevHandler, $type, $message, $file, $line, $context);
            });

            try {
                $annotation = \Swagger\scan($projectPath);

                return json_decode(json_encode($annotation));
            } finally {
                restore_error_handler();
            }
        }, $overwrite);
    }
}
