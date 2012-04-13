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
     * @var Boolean
     */
    private $isResource = false;

    public function __construct(array $data)
    {
        if (isset($data['formType'])) {
            $this->formType = $data['formType'];
        } else if (isset($data['filters'])) {
            foreach ($data['filters'] as $filter) {
                if (!isset($filter['name'])) {
                    throw new \InvalidArgumentException('A "filter" element has to contain a "name" attribute');
                }

                $name = $filter['name'];
                unset($filter['name']);

                $this->filters[$name] = $filter;
            }
        }

        if (isset($data['description'])) {
            $this->description = $data['description'];
        }

        $this->isResource = isset($data['resource']) && $data['resource'];
    }

    /**
     * @return array|null
     */
    public function getFilters()
    {
        return $this->filters;
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
     * @return Boolean
     */
    public function isResource()
    {
        return $this->isResource;
    }
}
