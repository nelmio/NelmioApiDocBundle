<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Describer;

use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use OpenApi\Annotations\OpenApi;
use OpenApi\Context;
use PHPUnit\Framework\TestCase;

abstract class AbstractDescriberTestCase extends TestCase
{
    /** @var DescriberInterface */
    protected $describer;

    protected function getOpenApiDoc(): OpenApi
    {
        $api = new OpenApi(['_context' => new Context()]);
        $this->describer->describe($api);

        return $api;
    }
}
