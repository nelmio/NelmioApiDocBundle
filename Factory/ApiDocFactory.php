<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Factory;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ApiDocFactory
 */
class ApiDocFactory
{
    const KEY_ANNOTATION = 'annotation';
    const KEY_RESOURCE   = 'resource';

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * Constructor
     *
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param array $documentation
     *
     * @return array
     */
    public function create(array $documentation)
    {
        $apiDoc = new ApiDoc($documentation);

        if (array_key_exists('route', $documentation) === true) {
            $apiDoc->setRoute($this->router->getRouteCollection()->get($documentation['route']));
        }

        return array(
            self::KEY_ANNOTATION => $apiDoc,
            self::KEY_RESOURCE   => $documentation[self::KEY_RESOURCE],
        );
    }
}
