<?php

<?php

/*
 * This file is part of the ApiDocBundle package.
 *
 * (c) EXSyst
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EXSyst\Bundle\ApiDocBundle\Describer;

use Doctrine\Common\Util\ClassUtils;
use EXSyst\Bundle\ApiDocBundle\RouteDescriber\RouteDescriberInterface;
use gossi\swagger\Swagger;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;

class ExternalDocDescriber implements DescriberInterface
{
    private $externalDoc;
    private $stategy;

    /**
     * @param array|callable $externalDoc
     * @param int            $strategy
     */
    public function __construct($externalDoc, $strategy = Swagger::PREFER_ORIGINAL)
    {
        $this->externalDoc = $externalDoc;
        $this->strategy = $strategy;
    }

    public function describe(Swagger $api)
    {
        $externalDoc = $this->getExternalDoc();
        $api->merge($externalDoc, $this->strategy);
    }

    private function getExternalDoc(): array
    {
        if (is_callable($this->externalDoc)) {
            return call_user_func($this->externalDoc);
        }

        return $this->externalDoc;
    }
}
