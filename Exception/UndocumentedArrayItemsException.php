<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Exception;

/**
 * @deprecated since 4.17, this exception is not used anymore
 */
class UndocumentedArrayItemsException extends \LogicException
{
    public function __construct(private readonly ?string $class = null, private readonly string $path = '')
    {
        $propertyName = '';
        if (null !== $this->class) {
            $propertyName = $this->class.'::';
        }
        $propertyName .= $this->path;

        parent::__construct(sprintf('Property "%s" is an array, but its items type isn\'t specified. You can specify that by using the type `string[]` for instance or `@OA\Property(type="array", @OA\Items(type="string"))`.', $propertyName));
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPath()
    {
        return $this->path;
    }
}
