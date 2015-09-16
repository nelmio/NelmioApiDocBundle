<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * AnnotationsProvider compiler pass.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class AnnotationsProviderCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $annotationsProviders = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.annotations_provider') as $id => $attributes) {
            $annotationsProviders[] = new Reference($id);
        }

        $container
            ->getDefinition('nelmio_api_doc.extractor.api_doc_extractor')
            ->replaceArgument(6, $annotationsProviders)
        ;
    }
}
