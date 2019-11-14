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

class NullTranslator implements TranslatorInterface
{
    public function translate($property, $context = null)
    {
    }

    public function setDefinitions(string $class): self
    {
        return $this;
    }
}
