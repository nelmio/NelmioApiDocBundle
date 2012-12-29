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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
    const FORMATTER_ID = 'nelmio_api_doc.formatter.%s_formatter';

    public function indexAction(Request $request, $_format)
    {
        $extractedDoc = $this->get('nelmio_api_doc.extractor.api_doc_extractor')->all();

        if (!$this->has($serviceId = sprintf(self::FORMATTER_ID, $_format))) {
            $serviceId = sprintf(self::FORMATTER_ID, 'html');
            $_format   = 'html';
        }

        $content = $this->get($serviceId)->format($extractedDoc);

        return new Response($content, 200, array(
            'Content-Type' => $this->getMimeType($request, $_format),
        ));
    }

    private function getMimeType(Request $request, $_format)
    {
        switch ($_format) {
            case 'wadl':
                return 'application/vnd.sun.wadl+xml';

            case 'markdown':
                $_format = 'txt';
                break;
        }

        return $request->getMimeType($_format);
    }
}
