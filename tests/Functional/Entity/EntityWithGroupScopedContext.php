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

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity;

use Nelmio\ApiDocBundle\ModelDescriber\EnumModelDescriber;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;

class EntityWithGroupScopedContext
{
    #[Groups(['plain', 'forced'])]
    #[Context(context: [EnumModelDescriber::FORCE_NAMES => true], groups: ['forced'])]
    public ArticleType81 $type;
}
