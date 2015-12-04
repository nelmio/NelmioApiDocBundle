<?php

namespace Nelmio\ApiDocBundle\Tests\Fixtures;

use FOS\RestBundle\Controller\Annotations\RequestParam;

/**
 * For BC FOSRestBundle < 2.0
 *
 * @Annotation
 * @Target("METHOD")
 *
 * @author Ener-Getick
 */
class RequestParamHelper extends RequestParam
{
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if ($key === 'array') {
                if (property_exists($this, 'map')) {
                    $this->map = $value;
                } else {
                    $this->array = $value;
                }
            } else {
                $this->$key = $value;
            }
        }
    }
}
