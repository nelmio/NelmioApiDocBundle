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

use Hateoas\Configuration\Annotation as Hateoas;

/**
 * User.
 *
 * @Hateoas\Relation(name="example", attributes={"foo":"bar"}, href="http://www.example.com")
 * @Hateoas\Relation(name="route", href=@Hateoas\Route("foo"))
 * @Hateoas\Relation(name="route", attributes={"foo":"bar"}, embedded=@Hateoas\Embedded("expr(foo)"))
 */
class BazingaUser
{

}
