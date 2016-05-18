<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Controller;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("has_role('ROLE_USER', 'ROLE_FOOBAR')")
  */
class TestSecuredController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="index action"
     * )
     */
    public function indexAction()
    {
        return new Response('tests');
    }
}
