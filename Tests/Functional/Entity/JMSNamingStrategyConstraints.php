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
use Nelmio\ApiDocBundle\Tests\Functional\TestKernel;
use Symfony\Component\Validator\Constraints as Assert;

if (TestKernel::isAnnotationsAvailable()) {
    class JMSNamingStrategyConstraints extends JMSNamingStrategyConstraints80
    {
    }
} else {
    class JMSNamingStrategyConstraints extends JMSNamingStrategyConstraints81
    {
    }
}
