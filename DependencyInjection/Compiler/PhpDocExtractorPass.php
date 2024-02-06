<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Enables the `PhpDocExtractor` manually if Symfony did not. Covers the cases where the `phpdocumentor/reflection-docblock` dependency is considered
 * dev only and not automatically enabled by Symfony because `nelmio/api-doc-bundle` (which requires it) is required for dev environment only.
 *
 * @see https://github.com/symfony/symfony/blob/6.1/src/Symfony/Bundle/FrameworkBundle/DependencyInjection/FrameworkExtension.php#L1889
 *
 * @internal
 */
final class PhpDocExtractorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('property_info.php_doc_extractor')) {
            $definition = $container->register('property_info.php_doc_extractor', 'Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor');
            $definition->addTag('property_info.description_extractor', ['priority' => -1000]);
            $definition->addTag('property_info.type_extractor', ['priority' => -1001]);
        }
    }
}
