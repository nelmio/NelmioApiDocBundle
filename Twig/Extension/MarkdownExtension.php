<?php

namespace Nelmio\ApiDocBundle\Twig\Extension;

use Michelf\MarkdownExtra;

class MarkdownExtension extends \Twig_Extension
{
    private $markdownParser;

    public function __construct()
    {
        $this->markdownParser = new MarkdownExtra();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('extra_markdown', array($this, 'markdown'), array('is_safe' => array('html'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'nelmio_api_doc';
    }

    public function markdown($text)
    {
        return $this->markdownParser->transform($text);
    }
}
