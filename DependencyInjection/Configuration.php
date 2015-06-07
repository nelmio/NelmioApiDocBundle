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
                ->booleanNode('default_sections_opened')->defaultTrue()->end()
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
                        ->arrayNode('body_format')
                            ->addDefaultsIfNotSet()
                            ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) { return array('default_format' => $v); })
                            ->end()
                            ->children()
                                ->arrayNode('formats')
                                    ->defaultValue(array('form', 'json'))
                                    ->prototype('scalar')->end()
                                ->end()
                                ->enumNode('default_format')
                                    ->values(array('form', 'json'))
                                    ->defaultValue('form')
                                ->end()
                            ->end()
                        ->end()
                        ->arrayNode('request_format')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->arrayNode('formats')
                                    ->defaultValue(array(
                                        'json' => 'application/json',
                                        'xml' => 'application/xml'
                                    ))
                                    ->prototype('scalar')->end()
                                ->end()
                                ->enumNode('method')
                                    ->values(array('format_param', 'accept_header'))
                                    ->defaultValue('format_param')
                                ->end()
                                ->scalarNode('default_format')->defaultValue('json')->end()
                            ->end()
                        ->end()
                        ->arrayNode('authentication')
                            ->children()
                                ->scalarNode('delivery')
                                    ->isRequired()
                                    ->validate()
                                        ->ifNotInArray(array('query', 'http', 'header'))
                                        ->thenInvalid("Unknown authentication delivery type '%s'.")
                                    ->end()
                                ->end()
                                ->scalarNode('name')->isRequired()->end()
                                ->enumNode('type')
                                    ->info('Required if http delivery is selected.')
                                    ->values(array('basic', 'bearer'))
                                ->end()
                                ->booleanNode('custom_endpoint')->defaultFalse()->end()
                            ->end()
                            ->validate()
                                ->ifTrue(function ($v) {
                                    return 'http' === $v['delivery'] && !$v['type'] ;
                                })
                                ->thenInvalid('"type" is required when using http delivery.')
                            ->end()
                            # http_basic BC
                            ->beforeNormalization()
                                ->ifTrue(function ($v) {
                                    return 'http_basic' === $v['delivery'];
                                })
                                ->then(function ($v) {
                                    $v['delivery'] = 'http';
                                    $v['type'] = 'basic';

                                    return $v;
                                })
                            ->end()
                            ->beforeNormalization()
                                ->ifTrue(function ($v) {
                                    return 'http' === $v['delivery'];
                                })
                                ->then(function ($v) {
                                    if ('http' === $v['delivery'] && !isset($v['name'])) {
                                        $v['name'] = 'Authorization';
                                    }

                                    return $v;
                                })
                            ->end()
                        ->end()
                        ->booleanNode('entity_to_choice')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('swagger')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('model_naming_strategy')->defaultValue('dot_notation')->end()
                        ->scalarNode('api_base_path')->defaultValue('/api')->end()
                        ->scalarNode('swagger_version')->defaultValue('1.2')->end()
                        ->scalarNode('api_version')->defaultValue('0.1')->end()
                        ->arrayNode('info')
                            ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('title')->defaultValue('Symfony2')->end()
                                    ->scalarNode('description')->defaultValue('My awesome Symfony2 app!')->end()
                                    ->scalarNode('TermsOfServiceUrl')->defaultNull()->end()
                                    ->scalarNode('contact')->defaultNull()->end()
                                    ->scalarNode('license')->defaultNull()->end()
                                    ->scalarNode('licenseUrl')->defaultNull()->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultFalse()->end()
                        ->scalarNode('file')->defaultValue('%kernel.cache_dir%/api-doc.cache')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
