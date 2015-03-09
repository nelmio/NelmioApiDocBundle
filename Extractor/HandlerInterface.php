<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

interface HandlerInterface
{
    /**
     * Parse route parameters in order to populate ApiDoc.
     *
     * @param \Nelmio\ApiDocBundle\Annotation\ApiDoc $annotation
     * @param array                                  $annotations
     * @param \Symfony\Component\Routing\Route       $route
     * @param \ReflectionMethod                      $method
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method);
}
