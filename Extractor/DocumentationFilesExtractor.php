<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Extractor;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Factory\ApiDocFactory;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DocumentationFilesExtractor
 */
class DocumentationFilesExtractor
{
    /**
     * @var ApiDocFactory
     */
    protected $apiDocFactory;

    /**
     * @var array
     */
    protected $documentationFilesConfiguration;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * Constructor
     *
     * @param ApiDocFactory $apiDocFactory
     * @param Kernel        $kernel
     * @param array         $documentationFilesConfiguration
     */
    public function __construct(ApiDocFactory $apiDocFactory, Kernel $kernel, array $documentationFilesConfiguration)
    {
        $this->apiDocFactory                   = $apiDocFactory;
        $this->documentationFilesConfiguration = $documentationFilesConfiguration;
        $this->kernel                          = $kernel;
    }

    /**
     * @param string $view
     *
     * @return array
     */
    public function extractFiles($view = ApiDoc::DEFAULT_VIEW)
    {
        $apiDocs = array();
        $files   = $this->findFiles();

        foreach ($files as $file) {
            if ($docsExtracted = $this->parseFile($file, $view)) {
                $apiDocs = array_merge($apiDocs, $docsExtracted);
            }
        }

        return $apiDocs;
    }

    /**
     * @return Finder
     */
    protected function findFiles()
    {
        $basePath      = $this->documentationFilesConfiguration['path'];
        $fileSystem    = new Filesystem();
        $finder        = new Finder();

        foreach ($this->kernel->getBundles() as $bundle) {
            $path       = $bundle->getPath() . $basePath;

            if ($fileSystem->exists($path)) {
                $finder->in($path);
            }
        }

        return $finder->files()->name('*.yml');
    }

    /**
     * @param SplFileInfo $file
     * @param string      $view
     *
     * @return array
     */
    protected function parseFile(SplFileInfo $file, $view = ApiDoc::DEFAULT_VIEW)
    {
        $docsExtracted = array();
        $docs          = Yaml::parse($file);

        if (is_array($docs)) {
            foreach ($docs as $doc) {
                $apiDoc = $this->apiDocFactory->create($doc, $view);
                if ($apiDoc !== false) {
                    $docsExtracted[] = $apiDoc;
                }
            }
        }

        return $docsExtracted;
    }
}
