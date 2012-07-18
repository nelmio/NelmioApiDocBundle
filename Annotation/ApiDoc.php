<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

/**
 * @Annotation
 */
class ApiDoc
{
    /**
     * @var array
     */
    private $filters  = array();

    /**
     * @var string
     */
    private $formType = null;

    /**
     * @var string
     */
    private $description = null;

    /**
     * @var string
     */
    private $documentation = null;

    /**
     * @var Boolean
     */
    private $isResource = false;

    public function __construct(array $data)
    {
        if (isset($data['formType'])) {
            $this->formType = $data['formType'];
        } elseif (isset($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (!isset($filter['name'])) {
                    throw new \InvalidArgumentException('A "filter" element has to contain a "name" attribute');
                }

                $name = $filter['name'];
                unset($filter['name']);

                $this->addFilter($name, $filter);
            }
        }

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        $this->isResource = isset($data['resource']) && $data['resource'];
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param string $name
     * @param array  $filter
     */
    public function addFilter($name, array $filter)
    {
        $this->filters[$name] = $filter;
    }

    /**
     * @return string|null
     */
    public function getFormType()
    {
        return $this->formType;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string|null
     */
    public function getDocumentation()
    {
        return $this->documentation;
    }

    /**
     * @param string $documentation
     */
    public function setDocumentation($documentation)
    {
        $this->documentation = $documentation;
    }

    /**
     * @return Boolean
     */
    public function isResource()
    {
        return $this->isResource;
    }
}
