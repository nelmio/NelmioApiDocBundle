<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\EventListener;

use Nelmio\ApiDocBundle\Extractor\ApiDocExtractor;
use Nelmio\ApiDocBundle\Formatter\FormatterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{
    /**
     * @var \Nelmio\ApiDocBundle\Extractor\ApiDocExtractor
     */
    protected $extractor;

    /**
     * @var \Nelmio\ApiDocBundle\Formatter\FormatterInterface
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $parameter;

    public function __construct(ApiDocExtractor $extractor, FormatterInterface $formatter, $parameter)
    {
        $this->extractor = $extractor;
        $this->formatter = $formatter;
        $this->parameter = $parameter;
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

        if (!$request->query->has($this->parameter)) {
            return;
        }

        $controller = $request->attributes->get('_controller');
        $route      = $request->attributes->get('_route');

        if (null !== $annotation = $this->extractor->get($controller, $route)) {
            $result = $this->formatter->formatOne($annotation);

            $event->setResponse(new Response($result, 200, array(
                'Content-Type' => 'text/html'
            )));
        }
    }
}
