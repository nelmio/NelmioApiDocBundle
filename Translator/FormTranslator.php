<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Translator;

class FormTranslator implements TranslatorInterface
{
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function translate($property, $context = null)
    {
    }
}
