<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\ModelDescriber\Fixtures\TypeInfo;

final class StoppableDescriberValueObject
{
    public function __construct(
        public string $value,
    ) {
    }
}

final class StoppableDescriberModel
{
    public StoppableDescriberValueObject $valueObject;

    public ?StoppableDescriberValueObject $nullableValueObject;

    /**
     * @var list<StoppableDescriberValueObject>
     */
    public array $valueObjects;
}
