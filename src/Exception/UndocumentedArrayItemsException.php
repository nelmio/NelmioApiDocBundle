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

trigger_deprecation(
    'nelmio/api-doc-bundle',
    '4.17.0',
    'The "%s" class is deprecated and will be removed in a future version',
    UndocumentedArrayItemsException::class,
);

/**
 * @deprecated since 4.17, this exception is not used anymore
 */
class UndocumentedArrayItemsException extends \LogicException
{
    private ?string $class;
    private string $path;

    public function __construct(?string $class = null, string $path = '')
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

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}
