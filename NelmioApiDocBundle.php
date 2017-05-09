<?php

namespace Jlpoveda\ApiDocBundle;

use Jlpoveda\ApiDocBundle\DependencyInjection\AnnotationsProviderCompilerPass;
use Jlpoveda\ApiDocBundle\DependencyInjection\SwaggerConfigCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Jlpoveda\ApiDocBundle\DependencyInjection\LoadExtractorParsersPass;
use Jlpoveda\ApiDocBundle\DependencyInjection\RegisterExtractorParsersPass;
use Jlpoveda\ApiDocBundle\DependencyInjection\ExtractorHandlerCompilerPass;

class NelmioApiDocBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoadExtractorParsersPass());
        $container->addCompilerPass(new RegisterExtractorParsersPass());
        $container->addCompilerPass(new ExtractorHandlerCompilerPass());
        $container->addCompilerPass(new AnnotationsProviderCompilerPass());
        $container->addCompilerPass(new SwaggerConfigCompilerPass());
    }
}
