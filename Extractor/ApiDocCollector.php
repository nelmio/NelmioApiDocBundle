<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Extractor;

class ApiDocCollector
{
    private $providers;

    public function addProvider($provider)
    {
        $this->providers []= $provider;
    }

    public function __construct()
    {
        $this->providers = array();
    }

    public function get($annotation)
    {
        $results = array();
        foreach ($this->providers as $provider) {
            $results []= $provider->get($annotation);
        }

        return $results;
    }
}