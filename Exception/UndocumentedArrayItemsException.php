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

class UndocumentedArrayItemsException extends \LogicException
{
    private $class;
    private $path;

    public function __construct(string $class = null, string $path = '')
    {
        $this->class = $class;
        $this->path = $path;

        $propertyName = '';
        if (null !== $class) {
            $propertyName = $class.'::';
        }
        $propertyName .= $path;

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
