<?php

namespace Nelmio\ApiDocBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RegisterExtractorParsersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('nelmio_api_doc.extractor.api_doc_extractor')) {
            return;
        }

        $definition = $container->getDefinition('nelmio_api_doc.extractor.api_doc_extractor');
        $parserList = $container->getParameter('nelmio_api_doc.parsers');

        //find registered parsers and sort by priority
        $sortedParsers = array();
        foreach ($container->findTaggedServiceIds('nelmio_api_doc.extractor.parser') as $id => $tagAttributes) {

            $class = $container->getDefinition($id)->getClass();
            $matches = [];
            if (preg_match('/^%(.*)%$/', $class, $matches)) {
                $class = $container->getParameter($matches[1]);
                $class = explode('\\', $class);
                $class = array_pop($class);
            }

            // Cause of BC the rule is only available for configured parsers
            if (in_array($class, $parserList) && !$parserList[$class]) {
                continue;
            }

            foreach ($tagAttributes as $attributes) {
                $priority = isset($attributes['priority']) ? $attributes['priority'] : 0;
                $sortedParsers[$priority][] = $id;
            }
        }

        //add parsers if any
        if (!empty($sortedParsers)) {
            krsort($sortedParsers);
            $sortedParsers = call_user_func_array('array_merge', $sortedParsers);

            //add method call for each registered parsers
            foreach ($sortedParsers as $id) {
                $definition->addMethodCall('addParser', array(new Reference($id)));
            }
        }
    }
}
