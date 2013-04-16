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

class SensioFrameworkExtraHandler implements HandlerInterface
{
    const CACHE_ANNOTATION_CLASS = 'Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache';

    public function handle(ApiDoc $annotation, $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if (is_a($annot, self::CACHE_ANNOTATION_CLASS)) {
                $annotation->setCache($annot->getMaxAge());
            }
        }
    }
}
