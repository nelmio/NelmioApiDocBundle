<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Fixtures\Services;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Extractor\ApiDocProviderInterface;

class ServiceReturningTabular implements
{
    public function get($annotation)
    {
        $tabularSection = new TabularSection(array('col1', 'col2'));
        $tabularSection->setTitle('TabularTitle');
        $tabularSection->addRow(array('1:1', '2:1'));
        $tabularSection->addRow(array('2:1', '2:2'));

        return $tabularSection;
    }
}