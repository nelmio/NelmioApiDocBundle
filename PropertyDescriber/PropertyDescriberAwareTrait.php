<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\PropertyDescriber;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;
use OpenApi\Generator;
use Symfony\Component\PropertyInfo\Type;

trait PropertyDescriberAwareTrait
{
    /**
     * @var PropertyDescriberInterface
     */
    protected $propertyDescriber;

    public function setPropertyDescriber(PropertyDescriberInterface $propertyDescriber)
    {
        $this->propertyDescriber = $propertyDescriber;
    }
}
