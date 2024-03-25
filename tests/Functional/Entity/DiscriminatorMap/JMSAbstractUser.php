<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\DiscriminatorMap;

use JMS\Serializer\Annotation as Serializer;
use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;

if (TestKernel::isAnnotationsAvailable()) {
    /**
     * @Serializer\Discriminator(map={
     *     "manager" = JMSManager::class,
     *     "administrator" = JMSAdministrator::class,
     * }, groups={"Default"})
     */
    abstract class JMSAbstractUser
    {
        /**
         * @Serializer\Type("string")
         *
         * @Serializer\Groups({"Default"})
         */
        public $username;
    }
} else {
    #[Serializer\Discriminator(map: ['manager' => JMSManager::class, 'administrator' => JMSAdministrator::class], groups: ['Default'])]
    abstract class JMSAbstractUser
    {
        #[Serializer\Type('string')]
        #[Serializer\Groups(['Default'])]
        public $username;
    }
}
