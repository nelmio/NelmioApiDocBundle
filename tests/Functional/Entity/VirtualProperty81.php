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

use JMS\Serializer\Annotation as Serializer;

/**
 * Class VirtualProperty.
 */
#[Serializer\ExclusionPolicy('all')]
#[Serializer\VirtualProperty(
    name: 'email',
    exp: 'object.user.email',
    options: [[Serializer\Type::class, ['string']]]
)]
class VirtualProperty81
{
    /**
     * @var int
     */
    #[Serializer\Type('integer')]
    #[Serializer\Expose]
    private $id;

    /**
     * @var User
     */
    private $user;

    #[Serializer\Accessor(getter: 'getFoo', setter: 'setFoo')]
    #[Serializer\Type('string')]
    #[Serializer\Expose]
    private $virtualprop;

    public function __construct()
    {
        $this->user = new User();
        $this->user->setEmail('dummy@test.com');
    }

    public function __call(string $name, array $arguments)
    {
        if ('getFoo' === $name || 'setFoo' === $name) {
            return 'Success';
        }

        throw new \LogicException(sprintf('%s::__call does not implement this function.', __CLASS__));
    }
}
