<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('nelmio_api_doc')
            ->children()
                ->arrayNode('documentation')
                    ->info('The documentation used as base')
                    ->example(['info' => ['title' => 'My App']])
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('routes')
                    ->info('Filter the routes that are documented')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('path_patterns')
                            ->example(['^/api', '^/api(?!/admin)'])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('models')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('use_jms')->defaultFalse()->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
