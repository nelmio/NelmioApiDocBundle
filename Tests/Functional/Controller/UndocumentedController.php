<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Tests\Functional\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UndocumentedController
{
    /**
     * This path is excluded by the config (only /api allowed).
     *
     * @Route("/undocumented", methods={"GET"})
     */
    public function undocumentedAction()
    {
    }
}
