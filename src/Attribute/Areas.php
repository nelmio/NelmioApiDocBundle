<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Attribute;

/**
 * @final
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Areas
{
    /** @var string[] */
    private array $areas;

    /**
     * @param string[]|array{value: string[]} $properties
     */
    public function __construct(array $properties)
    {
        if (array_key_exists('value', $properties) && is_array($properties['value'])) {
            trigger_deprecation('nelmio/api-doc-bundle', '4.36.1', 'Passing an array with key "value" to "%s" is deprecated, pass the list of strings directly.', __METHOD__);

            $this->areas = array_values($properties['value']);
        } else {
            $this->areas = [];
            foreach ($properties as $area) {
                if (!is_string($area)) {
                    throw new \InvalidArgumentException('An area must be given as a string');
                }

                if (!in_array($area, $this->areas, true)) {
                    $this->areas[] = $area;
                }
            }
        }

        if ([] === $this->areas) {
            throw new \InvalidArgumentException('An array of areas was expected');
        }
    }

    public function has(string $area): bool
    {
        return in_array($area, $this->areas, true);
    }
}
