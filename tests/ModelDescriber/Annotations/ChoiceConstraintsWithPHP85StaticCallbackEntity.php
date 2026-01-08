<?php

declare(strict_types=1);

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\ModelDescriber\Annotations;

use Symfony\Component\Validator\Constraints as Assert;

class ChoiceConstraintsWithPHP85StaticCallbackEntity
{
    #[Assert\Choice(callback: static function () {
        return ['test1', 'test2'];
    })]
    public string $property1;
}
