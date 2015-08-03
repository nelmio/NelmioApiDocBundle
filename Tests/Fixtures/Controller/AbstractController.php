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

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Email;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

abstract class AbstractController
{
    const OWN_SECTION = 'Blah';

    /**
     * @ApiDoc(
     *  description="index action",
     * )
     */
    public function indexAction()
    {
        return new Response('tests');
    }

    /**
     * @ApiDoc(
     *  description="index action",
     *  section="Blah",
     * )
     */
    public function ownAttributesAction()
    {
        return new Response('test');
    }
}
