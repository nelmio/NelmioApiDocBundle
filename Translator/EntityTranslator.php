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

use EXSyst\Component\Swagger\Schema;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

class EntityTranslator implements TranslatorInterface
{
    /** @var string */
    private $path;

    /** @var array */
    private $definitions;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Update property title on Schema.
     *
     * @param \ReflectionProperty $property
     * @param Schema              $context
     */
    public function translate($property, $context = null)
    {
        if (isset($this->definitions[$property->name])) {
            $context->setTitle($this->definitions[$property->name]);
        }
    }

    /**
     * populate $this->definitions
     * Find good translation file and parse it.
     *
     * this method must be called before translate()
     *
     * @throws \Exception
     *
     * @return EntityTranslator
     */
    public function setDefinitions(string $class): self
    {
        $finder = new Finder();
        $finder->files()->in($this->path)->contains($class);

        if ($finder->hasResults()) {
            //need have only one result (one file by entity)
            if (1 != $finder->count()) {
                throw new \Exception(sprintf('Error, multiple translation files found for entity %s', $class));
            }

            $definitions = Yaml::parse(current($finder)->getContents());
            if (isset($definitions[$class]['attributes'])) {
                $this->definitions = $definitions[$class]['attributes'];
            }
        }

        return $this;
    }
}
