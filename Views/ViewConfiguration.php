<?php
namespace Nelmio\ApiDocBundle\Views;

/**
 * @author Asmir Mustafic <asmir@nestpick.com>
 * Class ViewConfiguration
 */
class ViewConfiguration
{
    /**
     * @var array
     */
    private $include = array();

    /**
     * @var bool
     */
    private $includeEmpty = false;

    public function __construct(array $include = array(), $includeEmpty = false)
    {
        $this->include = $include;
        $this->includeEmpty = $includeEmpty;
    }

    /**
     * @return array
     */
    public function getInclude()
    {
        return $this->include;
    }

    /**
     * @return boolean
     */
    public function isIncludeEmpty()
    {
        return $this->includeEmpty;
    }
}