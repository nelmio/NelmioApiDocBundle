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
                    ->useAttributeAsKey('key')
                    ->info('The documentation used as base')
                    ->example(['info' => ['title' => 'My App']])
                    ->prototype('variable')->end()
                ->end()
                ->arrayNode('routes')
                    ->beforeNormalization()
                        ->ifTrue(function ($v) {
                            return is_array($v) && array_key_exists('path_patterns', $v) && is_array($v['path_patterns']) && !array_key_exists('path_patterns', $v['path_patterns']);
                        })
                        ->then(function ($v) {
                            $v['host'] = null;
                            return [$v];
                        })
                    ->end()
                    ->beforeNormalization()
                        ->ifTrue(function ($v) { // make sure that each host was mentioned once
                            $hosts = [];
                            foreach ($v as $arr) {
                                if (in_array($arr['host'], $hosts)) {
                                    return true;
                                }
                                $hosts[] = $arr['host'];
                            }
                            return false;
                        })
                        ->then(function ($v) {
                            $result = [];
                            foreach ($v as $arr){
                                foreach ($result as &$item) {
                                    if ($arr['host'] === $item['host']) {
                                        $item['path_patterns'] = array_unique(array_merge($item['path_patterns'], $arr['path_patterns']));
                                        continue 2;
                                    }
                                }
                                $result[] = $arr;
                            }
                            return $result;
                        })
                    ->end()
                    ->info('Filter the routes that are documented')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')
                                ->defaultValue(null)
                            ->end()
                            ->arrayNode('path_patterns')
                                ->example(['^/api', '^/api(?!/admin)'])
                                ->prototype('scalar')->end()
                            ->end()
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
