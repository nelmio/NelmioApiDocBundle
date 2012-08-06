<?php

namespace Nelmio\ApiDocBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Nelmio\ApiDocBundle\DependencyInjection\RegisterExtractorParsersPass;

class NelmioApiDocBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RegisterExtractorParsersPass());
    }
}
