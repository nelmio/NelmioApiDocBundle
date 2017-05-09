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

use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Jlpoveda\ApiDocBundle\Extractor\HandlerInterface;
use Jlpoveda\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;
use JMS\SecurityExtraBundle\Annotation\Secure;

class JmsSecurityExtraHandler implements HandlerInterface
{
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if ($annot instanceof PreAuthorize) {
                $annotation->setAuthentication(true);
            } elseif ($annot instanceof Secure) {
                $annotation->setAuthentication(true);
                $annotation->setAuthenticationRoles(is_array($annot->roles) ? $annot->roles : explode(',', $annot->roles));
            }
        }
    }
}
