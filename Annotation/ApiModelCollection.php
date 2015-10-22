<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Annotation;

/**
 * @Annotation
 * @Target({"ANNOTATION"})
 * @author Bez Hermoso <bez@activelamp.com>
 */
class ApiModelCollection extends ApiModel
{

    /**
     * @var string
     */
    protected $collectionName = '';

    /**
     * @param array $values
     */
    public function __construct(array $values)
    {
        parent::__construct($values);

        if (!empty($values['collectionName'])) {
            $this->collectionName = $values['collectionName'];
        }
    }

    /**
     * @return string
     */
    public function getCollectionName()
    {
        return $this->collectionName;
    }
}
