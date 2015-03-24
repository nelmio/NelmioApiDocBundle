<?php

 /**
  * This file is part of the NelmioApiDoc project.
  *
  * (c) BRAMILLE Sébastien <sebastien.bramille@gmail.com>
  *
  * For the full copyright and license information, please view the LICENSE
  * file that was distributed with this source code.
  */

namespace Nelmio\ApiDocBundle\Manager;

use Nelmio\ApiDocBundle\Factory\ApiDocFactory;
use Nelmio\ApiDocBundle\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class DocumentationFilesManager
 */
class DocumentationFilesManager
{
    /**
     * @var ApiDocFactory
     */
    protected $apiDocFactory;

    /**
     * Constructor
     *
     * @param ApiDocFactory $apiDocFactory
     */
    public function __construct(ApiDocFactory $apiDocFactory)
    {
        $this->apiDocFactory = $apiDocFactory;
    }

    /**
     * @param array|string[] $files
     *
     * @return bool
     */
    public function parse(array $files)
    {
        $output = array();
        foreach ($files as $file) {
            $fileSystem = new Filesystem();
            if (!$fileSystem->exists($file)) {
                throw new FileNotFoundException(null, 0, null, $file);
            }

            $fileDocumentations = Yaml::parse($file);

            if (is_array($fileDocumentations)) {
                foreach ($fileDocumentations as $documentation) {
                    $output[] = $this->apiDocFactory->create($documentation);
                }
            }
        }

        return $output;
    }
}
