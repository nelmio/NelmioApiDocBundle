<?php

namespace Nelmio\ApiBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiBundle\Formatter\FormatterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\RouterInterface;

class RequestListener
{
    protected $annotationClass = 'Nelmio\\ApiBundle\\Annotation\\ApiDoc';

    protected $reader;

    protected $router;

    protected $formatter;

    public function __construct(Reader $reader, RouterInterface $router, FormatterInterface $formatter)
    {
        $this->reader = $reader;
        $this->router = $router;
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

        preg_match('#(.+)::([\w]+)#', $request->get('_controller'), $matches);
        $method = new \ReflectionMethod($matches[1], $matches[2]);
        $route  = $request->get('_route');

        if ($annot = $this->reader->getMethodAnnotation($method, $this->annotationClass)) {
            if ($route = $this->router->getRouteCollection()->get($route)) {
                $result = $this->formatter->format($annot, $route);

                $event->setResponse(new JsonResponse($result));
            }
        }
    }
}
