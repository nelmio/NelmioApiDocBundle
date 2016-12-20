<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Jlpoveda\ApiDocBundle\Tests\Fixtures;

use Jlpoveda\ApiDocBundle\Util\LegacyFormHelper;

/**
 * This class is used to have dynamic annotations for BC.
 * {@see Jlpoveda\ApiDocBundle\Tests\Fixtures\Controller\TestController}
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
if (LegacyFormHelper::isLegacy()) {
    class DependencyTypePath
    {
        const TYPE = 'dependency_type';
    }
} else {
    class DependencyTypePath
    {
        const TYPE = 'Jlpoveda\ApiDocBundle\Tests\Fixtures\Form\DependencyType';
    }
}
