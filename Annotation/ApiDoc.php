<?php

namespace Nelmio\ApiBundle\Annotation;

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

        $this->isResource = isset($data['resource']);
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getFormType()
    {
        return $this->formType;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function isResource()
    {
        return $this->isResource;
    }
}
