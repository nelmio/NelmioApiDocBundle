<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jlpoveda\ApiDocBundle\Extractor\Handler;

use Jlpoveda\ApiDocBundle\Extractor\HandlerInterface;
use Jlpoveda\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class SensioFrameworkExtraHandler implements HandlerInterface
{
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if ($annot instanceof Cache) {
                $annotation->setCache($annot->getMaxAge());
            } elseif ($annot instanceof Security) {
                $annotation->setAuthentication(true);
            }
        }
    }
}
