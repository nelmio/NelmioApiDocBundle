<?php

namespace Nelmio\ApiDocBundle;

use Nelmio\ApiDocBundle\DependencyInjection\SwaggerConfigCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nelmio\ApiDocBundle\DependencyInjection\LoadExtractorParsersPass;
use Nelmio\ApiDocBundle\DependencyInjection\RegisterExtractorParsersPass;
use Nelmio\ApiDocBundle\DependencyInjection\ExtractorHandlerCompilerPass;

class NelmioApiDocBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new LoadExtractorParsersPass());
        $container->addCompilerPass(new RegisterExtractorParsersPass());
        $container->addCompilerPass(new ExtractorHandlerCompilerPass());
        $container->addCompilerPass(new SwaggerConfigCompilerPass());
    }
}
