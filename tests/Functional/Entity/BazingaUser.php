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
 */
#[Hateoas\Relation(name: 'example', attributes: ['str_att' => 'bar', 'float_att' => 5.6, 'bool_att' => false], href: 'http://www.example.com')]
#[Hateoas\Relation(name: 'route', href: new Hateoas\Route(name: 'foo'))]
#[Hateoas\Relation(
    name: 'route',
    attributes: ['foo' => 'bar'],
    embedded: new Hateoas\Embedded(content: "expr(service('xx'))")
)]
#[Hateoas\Relation(
    name: 'embed_with_group',
    attributes: ['foo' => 'with_groups'],
    exclusion: new Hateoas\Exclusion(groups: ['foo']),
    embedded: new Hateoas\Embedded(content: "expr(service('xx'))")
)]
class BazingaUser
{
}
