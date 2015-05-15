<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

/**
 * Interface for annotations providers.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
interface AnnotationsProviderInterface
{
    /**
     * Returns an array ApiDoc annotations.
     *
     * @return \Nelmio\ApiDocBundle\Annotation\ApiDoc[]
     */
    public function getAnnotations();
}
