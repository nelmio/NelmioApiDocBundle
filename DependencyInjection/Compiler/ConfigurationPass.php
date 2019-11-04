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

use Nelmio\ApiDocBundle\ModelDescriber\FormModelDescriber;
use Nelmio\ApiDocBundle\ModelDescriber\ObjectModelDescriber;
use Nelmio\ApiDocBundle\Translator\EntityTranslator;
use Nelmio\ApiDocBundle\Translator\NullTranslator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enables the FormModelDescriber only if forms are enabled.
 *
 * @internal
 */
final class ConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        //check if translation is defined in Nelmio config
        if ($container->hasParameter('nelmio_translator_paths')) {
            $paths = $container->getParameter('nelmio_translator_paths');
        } else {
            $paths = [];
        }

        if ($container->hasDefinition('form.factory')) {
            $container->register('nelmio_api_doc.model_describers.form', FormModelDescriber::class)
                ->setPublic(false)
                ->addArgument(new Reference('form.factory'))
                ->addTag('nelmio_api_doc.model_describer', ['priority' => 100]);
        }

        //translation path found for ObjectNormalizer, we inject EntityTranslator
        if (isset($paths['entity'])) {
            $container->register('nelmio_entity_translator', EntityTranslator::class)
                        ->setPublic(false)
                        ->addArgument($paths['entity']);
        } else {
            //no translation path for ObjectNormalizer, we inject NullObject Tranlator
            $container->register('nelmio_entity_translator', NullTranslator::class);
        }

        $container->register('nelmio_api_doc.model_describers.object', ObjectModelDescriber::class)
            ->setPublic(false)
            ->addArgument(new Reference('property_info'))
            ->addArgument(new Reference('annotation_reader'))
            ->addArgument(new Reference('nelmio_entity_translator'))
            ->addTag('nelmio_api_doc.model_describer');
    }
}
