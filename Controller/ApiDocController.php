<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Controller;

use Nelmio\ApiDocBundle\Formatter\RequestAwareSwaggerFormatter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
    public function indexAction(Request $request)
    {
        $extractor = $this->get('nelmio_api_doc.extractor.api_doc_extractor');
        $formatter = $this->get('nelmio_api_doc.formatter.html_formatter');
        $apiVersion = $request->query->get('_version', null);

        if ($apiVersion) {
            $formatter->setVersion($apiVersion);
            $extractedDoc = $extractor->allForVersion($apiVersion);
        } else {
            $extractedDoc = $extractor->all();
        }

        $htmlContent = $formatter->format($extractedDoc);

        return new Response($htmlContent, 200, array('Content-Type' => 'text/html'));
    }

    public function swaggerAction(Request $request, $resource = null)
    {

        $docs = $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all();
        $formatter = new RequestAwareSwaggerFormatter($request, $this->get('nelmio_api_doc.formatter.swagger_formatter'));

        $spec = $formatter->format($docs, $resource ? '/' . $resource : null);

        if ($resource !== null && count($spec['apis']) === 0) {
            throw $this->createNotFoundException(sprintf('Cannot find resource "%s"', $resource));
        }

        return new JsonResponse($spec);
    }
}
