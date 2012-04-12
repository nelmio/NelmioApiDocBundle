<?php

namespace Nelmio\ApiBundle\EventListener;

use Nelmio\ApiBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiBundle\Formatter\FormatterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    /**
     * @var \Nelmio\ApiBundle\Extractor\ApiDocExtractor
     */
    protected $extractor;

    /**
     * @var \Nelmio\ApiBundle\Formatter\FormatterInterface
     */
    protected $formatter;

    public function __construct(ApiDocExtractor $extractor, FormatterInterface $formatter)
    {
        $this->extractor = $extractor;
        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->get('_doc')) {
            return;
        }

        $controller = $request->get('_controller');
        $route      = $request->get('_route');

        if (null !== $array = $this->extractor->get($controller, $route)) {
            $result = $this->formatter->formatOne($array['annotation'], $array['route']);

            $event->setResponse(new Response($result, 200, array(
                'Content-Type' => 'text/html'
            )));
        }
    }
}
