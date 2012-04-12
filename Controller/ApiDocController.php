<?php

namespace Nelmio\ApiDocBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
    public function indexAction()
    {
        $extractedDoc = $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all();
        $htmlContent  = $this->get('nelmio_api_doc.formatter.html_formatter')->format($extractedDoc);

        return new Response($htmlContent);
    }
}
