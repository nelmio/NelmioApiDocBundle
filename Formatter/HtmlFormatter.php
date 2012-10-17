<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;

use Symfony\Component\Templating\EngineInterface;

class HtmlFormatter extends AbstractFormatter
{
    /**
     * @var array
     */
    private $authentication;

    /**
     * @var string
     */
    private $apiName;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * @var boolean
     */
    private $enableSandbox;

    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $engine;

    /**
     * @var string
     */
    private $requestFormatMethod;

    /**
     * @var string
     */
    private $defaultRequestFormat;

    /**
     * @param array $authentication
     */
    public function setAuthentication(array $authentication = null)
    {
        $this->authentication = $authentication;
    }

    /**
     * @param string $apiName
     */
    public function setApiName($apiName)
    {
        $this->apiName = $apiName;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint)
    {
        $this->endpoint = $endpoint;
    }

    /**
     * @param boolean $enableSandbox
     */
    public function setEnableSandbox($enableSandbox)
    {
        $this->enableSandbox = $enableSandbox;
    }

    /**
     * @param EngineInterface $engine
     */
    public function setTemplatingEngine(EngineInterface $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param string $method
     */
    public function setRequestFormatMethod($method)
    {
        $this->requestFormatMethod = $method;
    }

    public function setDefaultRequestFormat($format)
    {
        $this->defaultRequestFormat = $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        if (isset($data['parameters'])) {
            $data['parameters'] = $this->compressNestedParameters($data['parameters'], null, true);
        }

        if (isset($data['response'])) {
            $data['response'] = $this->compressNestedParameters($data['response']);
        }

        return $this->engine->render('NelmioApiDocBundle::resource.html.twig', array_merge(
            array('data' => $data, 'displayContent' => true),
            $this->getGlobalVars()
        ));
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        $processedCollection = array();

        foreach ($collection as $path => $methods) {
            $processedCollection[$path] = array();
            foreach ($methods as $method) {
                if (isset($method['parameters'])) {
                    $method['parameters'] = $this->compressNestedParameters($method['parameters'], null, true);
                }

                if (isset($method['response'])) {
                    $method['response'] = $this->compressNestedParameters($method['response']);
                }

                $processedCollection[$path][] = $method;
            }
        }

        return $this->engine->render('NelmioApiDocBundle::resources.html.twig', array_merge(
            array('resources' => $processedCollection),
            $this->getGlobalVars()
        ));
    }

    /**
     * @return array
     */
    private function getGlobalVars()
    {
        return array(
            'apiName'              => $this->apiName,
            'authentication'       => $this->authentication,
            'endpoint'             => $this->endpoint,
            'enableSandbox'        => $this->enableSandbox,
            'requestFormatMethod'  => $this->requestFormatMethod,
            'defaultRequestFormat' => $this->defaultRequestFormat,
            'date'                 => date(DATE_RFC822),
            'css'                  => file_get_contents(__DIR__ . '/../Resources/public/css/screen.css'),
            'js'                   => file_get_contents(__DIR__ . '/../Resources/public/js/all.js'),
        );
    }

}
