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
 * @internal
 */
final class TagDescribersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.describer') as $id => $tags) {
            $describer = $container->getDefinition($id);
            foreach ($container->getParameter('nelmio_api_doc.areas') as $area) {
                foreach ($tags as $tag) {
                    $describer->addTag(sprintf('nelmio_api_doc.describer.%s', $area), $tag);
                }
            }
        }
    }
}
