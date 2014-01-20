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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root('nelmio_api_doc')
            ->children()
                ->scalarNode('name')->defaultValue('API documentation')->end()
                ->arrayNode('exclude_sections')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('motd')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('template')->defaultValue('NelmioApiDocBundle::Components/motd.html.twig')->end()
                    ->end()
                ->end()
                ->arrayNode('request_listener')
                    ->beforeNormalization()
                        ->ifTrue(function ($a) { return is_bool($a); })
                        ->then(function ($a) { return array('enabled' => $a); })
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultTrue()->end()
                        ->scalarNode('parameter')->defaultValue('_doc')->end()
                    ->end()
                ->end()
                ->arrayNode('sandbox')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultTrue()->end()
                        ->scalarNode('endpoint')->defaultNull()->end()
                        ->scalarNode('accept_type')->defaultNull()->end()
                        ->enumNode('body_format')
                            ->values(array('form', 'json'))
                            ->defaultValue('form')
                        ->end()
                        ->arrayNode('request_format')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->enumNode('method')
                                    ->values(array('format_param', 'accept_header'))
                                    ->defaultValue('format_param')
                                ->end()
                                ->enumNode('default_format')
                                    ->values(array('json', 'xml'))
                                    ->defaultValue('json')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('authentication')
                            ->children()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('delivery')
                                    ->isRequired()
                                    ->validate()
                                        ->ifNotInArray(array('query', 'http_basic', 'header'))
                                        ->thenInvalid("Unknown authentication delivery type '%s'.")
                                    ->end()
                                ->end()
                                ->booleanNode('custom_endpoint')->defaultFalse()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
