<?php

namespace Nelmio\ApiDocBundle\Swagger2;

use Nelmio\ApiDocBundle\Swagger2\Segment\Path;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ExpandedDefinition
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class ExpandedDefinition implements SegmentInterface
{
    /**
     * @var array
     */
    private $info;
    
    /**
     * @var array
     */
    private $schemes;
    
    /**
     * @var array
     */
    private $consumes;

    /**
     * @var array
     */
    private $produces;

    /**
     * @var array
     */
    private $paths = array();

    public function __construct($basePath, array $info, array $schemes, array $consumes, array $produces)
    {
        $this->basePath = $basePath;
        $this->info = $info;
        $this->schemes = $schemes;
        $this->consumes = $consumes;
        $this->produces = $produces;
    }

    public function toArray()
    {
        return array(
            'swagger' => '2.0',
            'info' => $this->getInfo(),
            'host' => $this->getHost(),
            'basePath' => $this->getBasePath(),
            'schemes' => $this->getSchemes(),
            'consumes' => $this->getConsumes(),
            'produces' => $this->getProduces(),
            'paths' => $this->getPaths(),
            'definitions' => $this->getDefinitions(),
        );
    }

    private function getSchemes()
    {
        return count($this->schemes) ? array_unique($this->schemes) : array('http');
    }

    public function getConsumes()
    {
        return count($this->consumes) ? array_unique($this->consumes) : array('application/json');
    }

    public function getProduces()
    {
        return count($this->produces) ? array_unique($this->produces) : array('application/json');
    }

    public function getInfo()
    {
        return $this->info;
    }

    public function getHost()
    {
        return '';
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function addPath(Path $path)
    {
        $url = $path->getUrl();
        if (!isset($this->paths[$url])) {
            $this->paths[$url] = array();
        }
        $this->paths[$url][] = $path;
    }

    private function getPaths()
    {
        $pathsArray = array();

        foreach ($this->paths as $url => $paths) {
            $data = array();
            $parameters = array();
            foreach ($paths as $path) {
                $data = array_fill_keys($path->getMethods(), $path->toArray());
                foreach ($path->getPathParameters() as $pathParameter) {
                    $paramName = $pathParameter["name"];
                    if (!isset($parameters[$paramName])) {
                        $parameters[$paramName] = $pathParameter;
                    } else {
                        $parameters[$paramName] = array_merge($parameters[$paramName], $pathParameter);
                    }
                }
            }

            if (count($parameters)) {
                $data['parameters'] = array_values($parameters);
            }

            if (count($data)) {
                $pathsArray[$url] = $data;
            }
        }

        return $pathsArray;
    }

    private function getDefinitions()
    {
        return array();
    }

    public function toJson($options = null)
    {
        return json_encode($this->toArray(), $options);
    }

    public function toYaml()
    {
        return Yaml::dump($this->toArray());
    }
}
