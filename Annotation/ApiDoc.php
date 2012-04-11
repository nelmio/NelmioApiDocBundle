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
    private $comment = null;

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

        if (isset($data['comment'])) {
            $this->comment = $data['comment'];
        }
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function getFormType()
    {
        return $this->formType;
    }

    public function getComment()
    {
        return $this->comment;
    }
}
