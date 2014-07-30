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

class ResourceController 
{
    /**
     * @ApiDoc(
     *      resource=true,
     *      resourceDescription="Operations on resource.",
     *      description="List resources.",
     *      statusCodes={200 = "Returned on success.", 404 = "Returned if resource cannot be found."}
     * )
     */
    public function listResourcesAction()
    {

    }

    /**
     * @ApiDoc(description="Retrieve a resource by ID.")
     */
    public function getResourceAction()
    {

    }

    /**
     * @ApiDoc(description="Delete a resource by ID.")
     */
    public function deleteResourceAction()
    {

    }

    /**
     * @ApiDoc(
     *      description="Create a new resource.",
     *      input={"class" = "Nelmio\ApiDocBundle\Tests\Fixtures\Form\SimpleType", "name" = ""},
     *      output="Nelmio\ApiDocBundle\Tests\Fixtures\Model\JmsNested"
     * )
     */
    public function createResourceAction()
    {

    }

    /**
     * @ApiDoc(resource=true, description="List another resource.", resourceDescription="Operations on another resource.")
     */
    public function listAnotherResourcesAction()
    {

    }

    /**
     * @ApiDoc(description="Retrieve another resource by ID.")
     */
    public function getAnotherResourceAction()
    {

    }

    /**
     * @ApiDoc(description="Update a resource bu ID.")
     */
    public function updateAnotherResourceAction()
    {

    }
}