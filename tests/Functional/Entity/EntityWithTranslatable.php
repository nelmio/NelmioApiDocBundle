<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;

class EntityWithTranslatable
{
    public TranslatableInterface $translatable;
    public TranslatableMessage $translatableMessage;

    public function __construct(TranslatableInterface $translatable, TranslatableMessage $translatableMessage)
    {
        $this->translatable = $translatable;
        $this->translatableMessage = $translatableMessage;
    }
}
