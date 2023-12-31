<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Functional\Controller;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Regex;

class FOSRestController81
{
    #[Route('/fosrest.{_format}', methods: ['POST'])]
    #[QueryParam(name: 'foo', requirements: new Regex('/^\d+$/'))]
    #[QueryParam(name: 'mapped', map: true)]
    #[RequestParam(name: 'Barraa', key: 'bar', requirements: '\d+')]
    #[RequestParam(name: 'baz', requirements: new IsTrue())]
    #[RequestParam(name: 'datetime', requirements: new DateTime('Y-m-d\TH:i:sP'))]
    #[RequestParam(name: 'datetimeAlt', requirements: new DateTime('c'))]
    #[RequestParam(name: 'datetimeNoFormat', requirements: new DateTime())]
    #[RequestParam(name: 'date', requirements: new DateTime('Y-m-d'))]
    public function fosrestAction()
    {
    }

    #[Route('/fosrest_attributes.{_format}', methods: ['POST'])]
    #[QueryParam(name: 'foo', requirements: new Regex('/^\d+$/'))]
    #[QueryParam(name: 'mapped', map: true)]
    #[RequestParam(name: 'Barraa', key: 'bar', requirements: '\d+')]
    #[RequestParam(name: 'baz', requirements: new IsTrue())]
    #[RequestParam(name: 'datetime', requirements: new DateTime('Y-m-d\TH:i:sP'))]
    #[RequestParam(name: 'datetimeAlt', requirements: new DateTime('c'))]
    #[RequestParam(name: 'datetimeNoFormat', requirements: new DateTime())]
    #[RequestParam(name: 'date', requirements: new DateTime('Y-m-d'))]
    public function fosrestAttributesAction()
    {
    }
}
