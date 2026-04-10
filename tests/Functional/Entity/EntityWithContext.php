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

class EntityWithContext
{
    public ArticleType81 $type;

    #[Context([EnumModelDescriber::FORCE_NAMES => true])]
    public ArticleType81 $typeWithForceNames;
}
