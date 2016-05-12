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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ApiDocFactory
 */
class ApiDocFactory
{
    const KEY_ANNOTATION = 'annotation';
    const KEY_RESOURCE   = 'resource';
    const KEY_VIEWS      = 'views';
    const KEY_ROUTE      = 'route';

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
     * @return boolean|array
     */
    public function create(array $documentation, $view = ApiDoc::DEFAULT_VIEW)
    {
        $apiDoc   = new ApiDoc($documentation);
        $resource = false;

        if ((0 === count($apiDoc->getViews()) && ($view !== ApiDoc::DEFAULT_VIEW)) || (0 !== count($apiDoc->getViews()) && !in_array($view, $apiDoc->getViews()))) {
            return false;
        }

        if (array_key_exists(self::KEY_ROUTE, $documentation)) {
            if (($route = $this->router->getRouteCollection()->get($documentation[self::KEY_ROUTE])) !== null) {
                $apiDoc->setRoute($route);
            } else {
                throw new NotFoundHttpException(sprintf('Route %s not found', $documentation[self::KEY_ROUTE]));
            }
        }

        if (array_key_exists(self::KEY_RESOURCE, $documentation)) {
            $resource = $documentation[self::KEY_RESOURCE];
        }

        return array(
            self::KEY_ANNOTATION => $apiDoc,
            self::KEY_RESOURCE   => $resource,
        );
    }
}
