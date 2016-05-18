<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor\Handler;

use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
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

                preg_match("/has_role\((.*)\)/", $annot->getExpression(), $matches);
                $securityRoles = array_map('trim', explode(',', $matches[1]));

                $roles = $annotation->getAuthenticationRoles();
                $roles = array_merge($roles, str_replace("'", "", $securityRoles));

                $annotation->setAuthenticationRoles(array_unique($roles));
            }
        }
    }
}
