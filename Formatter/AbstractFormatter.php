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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Parser\FormTypeParser;
use Symfony\Component\Routing\Route;

abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * @var \Nelmio\ApiDocBundle\Parser\FormTypeParser
     */
    protected $parser;

    public function __construct(FormTypeParser $parser)
    {
        $this->parser = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $apiDoc, Route $route)
    {
        return $this->renderOne($this->getData($apiDoc, $route));
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $collection)
    {
        $array = array();
        foreach ($collection as $coll) {
            $resource = $coll['resource'];
            if (!isset($array[$resource])) {
                $array[$resource] = array();
            }

            $array[$resource][] = $this->getData($coll['annotation'], $coll['route'], $coll['requirements']);
        }

        return $this->render($array);
    }

    /**
     * Format a single array of data
     *
     * @param  array        $data
     * @return string|array
     */
    abstract protected function renderOne(array $data);

    /**
     * Format a set of resource sections.
     *
     * @param  array        $collection
     * @return string|array
     */
    abstract protected function render(array $collection);

    /**
     * @param  ApiDoc $apiDoc
     * @param  Route  $route
     * @param  array  $requirements
     * @return array
     */
    protected function getData(ApiDoc $apiDoc, Route $route, array $requirements = array())
    {
        $method = $route->getRequirement('_method');
        $data   = array(
            'method' => $method ?: 'ANY',
            'uri'    => $route->compile()->getPattern(),
        );

        foreach ($route->compile()->getRequirements() as $name => $value) {
            if ('_method' !== $name) {
                $requirements[$name] = array(
                    'requirement'   => $value,
                    'type'          => '',
                    'description'   => '',
                );
            }
        }

        if (null !== $paramDocs = $route->getOption('_paramDocs')) {
            $regexp = '{(\w*) *\$%s *(.*)}i';
            foreach ($route->compile()->getVariables() as $var) {
                $found = false;
                foreach ($paramDocs as $paramDoc) {
                    if (preg_match(sprintf($regexp, preg_quote($var)), $paramDoc, $matches)) {
                        $requirements[$var]['type']        = isset($matches[1]) ? $matches[1] : '';
                        $requirements[$var]['description'] = $matches[2];

                        if (!isset($requirements[$var]['requirement'])) {
                            $requirements[$var]['requirement'] = '';
                        }

                        $found = true;
                        break;
                    }
                }

                if (!isset($requirements[$var]) && false === $found) {
                    $requirements[$var] = array('requirement' => '', 'type' => '', 'description' => '');
                }
            }
        }

        $data['requirements'] = $requirements;

        if (null !== $formType = $apiDoc->getFormType()) {
            $data['parameters'] = $this->parser->parse($formType);

            if ('PUT' === $method) {
                // All parameters are optional with PUT (update)
                array_walk($data['parameters'], function($val, $key) use (&$data) {
                    $data['parameters'][$key]['required'] = false;
                });
            }
        }

        if ($filters = $apiDoc->getFilters()) {
            $data['filters'] = $filters;
        }

        if ($description = $apiDoc->getDescription()) {
            $data['description'] = $description;
        }

        if ($documentation = $apiDoc->getDocumentation()) {
            $data['documentation'] = $documentation;
        }

        return $data;
    }
}
