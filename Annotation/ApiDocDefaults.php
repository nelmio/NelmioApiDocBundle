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

use Symfony\Component\Routing\Route;

/**
 * @Annotation
 */
class ApiDocDefaults
{
    /**
     * Section to group actions together.
     *
     * @var string
     */
    private $section = null;

    public function __construct(array $data)
    {
        if (isset($data['section'])) {
            $this->section = $data['section'];
        }
    }

    /**
     * @param string $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return string
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();

        if ($section = $this->section) {
            $data['section'] = $section;
        }

        return $data;
    }
}
