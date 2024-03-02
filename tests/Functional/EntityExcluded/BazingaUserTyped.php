<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\EntityExcluded;

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * @Hateoas\Relation(
 *     name="typed_bazinga_users",
 *     embedded=@Hateoas\Embedded(
 *      "expr(service('zz'))",
 *      type="array<Nelmio\ApiDocBundle\Tests\Functional\Entity\BazingaUser>"
 *     )
 * )
 * @Hateoas\Relation(
 *     name="typed_bazinga_name",
 *     embedded=@Hateoas\Embedded(
 *      "expr(service('yy'))",
 *      type="string"
 *     )
 * )
 */
class BazingaUserTyped
{
}
