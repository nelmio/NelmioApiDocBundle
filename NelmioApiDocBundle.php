<?php

namespace Nelmio\ApiDocBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nelmio\ApiDocBundle\DependencyInjection\RegisterJmsParserPass;
use Nelmio\ApiDocBundle\DependencyInjection\RegisterExtractorParsersPass;
use Nelmio\ApiDocBundle\DependencyInjection\ExtractorHandlerCompilerPass;

class NelmioApiDocBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterJmsParserPass());
        $container->addCompilerPass(new RegisterExtractorParsersPass());
        $container->addCompilerPass(new ExtractorHandlerCompilerPass());
    }
}
