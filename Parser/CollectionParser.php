<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Parser;

use Nelmio\ApiDocBundle\DataTypes;

/**
 * Handles models that are specified as collections.
 *
 * @author Bez Hermoso <bez@activelamp.com>
 */
class CollectionParser implements ParserInterface, PostParserInterface
{

    /**
     * Return true/false whether this class supports parsing the given class.
     *
     * @param array $item containing the following fields: class, groups. Of which groups is optional
     *
     * @return boolean
     */
    public function supports(array $item)
    {
        return isset($item['collection']) && $item['collection'] === true;
    }

    /**
     * This doesn't parse anything at this stage.
     *
     * @param array $item
     *
     * @return array
     */
    public function parse(array $item)
    {
        return array();
    }

    /**
     * @param array|string $item       The string type of input to parse.
     * @param array        $parameters The previously-parsed parameters array.
     *
     * @return array
     */
    public function postParse(array $item, array $parameters)
    {
        $origParameters = $parameters;

        foreach ($parameters as $name => $body) {
            $parameters[$name] = null;
        }

        $collectionName = isset($item['collectionName']) ? $item['collectionName'] : '';

        $parameters[$collectionName] = array(
            'dataType' => null, // Delegates to ApiDocExtractor#generateHumanReadableTypes
            'subType' => $item['class'],
            'actualType' => DataTypes::COLLECTION,
            'readonly' => true,
            'required' => true,
            'default' => true,
            'description' => '',
            'children' => $origParameters,
        );

        return $parameters;
    }
}
