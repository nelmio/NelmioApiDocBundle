<?php

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

abstract class AbstractChoiceCallbackEntity
{
    #[Assert\Choice(callback: 'getStaticChoices')]
    public string $staticCallback;

    #[Assert\Choice(callback: 'getAbstractChoices')]
    public string $abstractCallback;

    #[Assert\Choice(callback: 'getInstanceChoices')]
    public string $nonStaticCallback;

    /**
     * @return string[]
     */
    public static function getStaticChoices(): array
    {
        return ['test1', 'test2'];
    }

    /**
     * @return string[]
     */
    abstract public static function getAbstractChoices(): array;

    /**
     * @return string[]
     */
    public function getInstanceChoices(): array
    {
        return ['test1', 'test2'];
    }
}
