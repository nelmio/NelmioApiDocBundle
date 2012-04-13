<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
            ;

        return $treeBuilder;
    }
}
