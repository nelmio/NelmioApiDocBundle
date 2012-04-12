<?php

namespace Nelmio\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
    public function indexAction()
    {
        $extractedDoc = $this->get('nelmio.api.extractor.api_doc_extractor')->all();
        $htmlContent  = $this->get('nelmio.api.formatter.html_formatter')->format($extractedDoc);

        return new Response($htmlContent);
    }
}
