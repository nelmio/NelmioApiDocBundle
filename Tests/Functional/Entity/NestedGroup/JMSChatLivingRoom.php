<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Entity\NestedGroup;

use JMS\Serializer\Annotation as Serializer;

if (\PHP_VERSION_ID >= 80100) {
    /**
     * User.
     */
    #[Serializer\ExclusionPolicy('all')]
    class JMSChatLivingRoom
    {
        #[Serializer\Type('integer')]
        #[Serializer\Expose]
        private $id;
    }
} else {
    /**
     * User.
     *
     * @Serializer\ExclusionPolicy("all")
     */
    class JMSChatLivingRoom
    {
        /**
         * @Serializer\Type("integer")
         * @Serializer\Expose
         */
        private $id;
    }
}
