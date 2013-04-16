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

class FosRestHandler implements HandlerInterface
{
    const FOS_REST_REQUEST_PARAM_CLASS    = 'FOS\\RestBundle\\Controller\\Annotations\\RequestParam';
    const FOS_REST_QUERY_PARAM_CLASS      = 'FOS\\RestBundle\\Controller\\Annotations\\QueryParam';

    public function handle(ApiDoc $annotation, $annotations, Route $route, \ReflectionMethod $method)
    {
        foreach ($annotations as $annot) {
            if (is_a($annot, self::FOS_REST_REQUEST_PARAM_CLASS)) {
                $annotation->addParameter($annot->name, array(
                    'required'    => $annot->strict && $annot->default === null,
                    'dataType'    => $annot->requirements,
                    'description' => $annot->description,
                    'readonly'    => false
                ));
            } elseif (is_a($annot, self::FOS_REST_QUERY_PARAM_CLASS)) {
                if ($annot->strict && $annot->default === null) {
                    $annotation->addRequirement($annot->name, array(
                        'requirement'   => $annot->requirements,
                        'dataType'      => '',
                        'description'   => $annot->description,
                    ));
                } else {
                    $annotation->addFilter($annot->name, array(
                        'requirement'   => $annot->requirements,
                        'description'   => $annot->description,
                    ));
                }
            }
        }
    }
}
