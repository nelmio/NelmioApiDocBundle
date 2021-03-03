<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Describer;

use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use OpenApi\Annotations as OA;

class ExternalDocDescriber implements DescriberInterface
{
    private $externalDoc;

    private $overwrite;

    /**
     * @param array|callable $externalDoc
     */
    public function __construct($externalDoc, bool $overwrite = false)
    {
        $this->externalDoc = $externalDoc;
        $this->overwrite = $overwrite;
    }

    public function describe(OA\OpenApi $api)
    {
        $externalDoc = $this->getExternalDoc();

        if (!empty($externalDoc)) {
            Util::merge($api, $externalDoc, $this->overwrite);
        }
    }

    private function getExternalDoc()
    {
        if (is_callable($this->externalDoc)) {
            return call_user_func($this->externalDoc);
        }

        return $this->externalDoc;
    }
}
