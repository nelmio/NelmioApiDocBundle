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

class JMSAdministrator extends JMSAbstractUser
{
    #[Serializer\Type('string')]
    #[Serializer\Groups(['Default'])]
    public $adminTitle;
}
