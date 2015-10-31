<?php

namespace Nelmio\ApiDocBundle\Swagger2;


use Symfony\Component\Yaml\Yaml;

/**
 * Class ExpandedDefinition
 *
 * @author Bez Hermoso <bezalelhermoso@gmail.com>
 */
class ExpandedDefinition implements Segment
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

    public function toJson($options = null)
    {
        return json_encode($this->toArray(), $options);
    }

    public function toYaml()
    {
        return Yaml::dump($this->toArray());
    }
}
