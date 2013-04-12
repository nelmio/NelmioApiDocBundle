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
use \Nelmio\ApiDocBundle\Annotation\ApiDoc;

class FosRestRequestParamHandler implements HandlerInterface
{
    const FOS_REST_REQUEST_PARAM_CLASS    = 'FOS\\RestBundle\\Controller\\Annotations\\RequestParam';

    public function handle(ApiDoc $annotation, $annotations)
    {
        foreach ($annotations as $annot) {
            if (is_a($annot, self::FOS_REST_REQUEST_PARAM_CLASS)) {
                $annotation->addParameter($annot->name, array(
                    'required'    => $annot->strict && $annot->default === null,
                    'dataType'    => $annot->requirements,
                    'description' => $annot->description,
                    'readonly'    => false
                ));
            }
        }
    }
}
