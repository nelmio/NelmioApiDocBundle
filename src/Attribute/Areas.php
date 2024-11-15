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

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final class Areas
{
    /**
     * @param string[] $areas
     */
    public function __construct(
        private array $areas,
    ) {
        foreach ($areas as $area) {
            if (!\is_string($area)) {
                throw new \InvalidArgumentException('An area must be given as a string');
            }
        }
    }

    public function has(string $area): bool
    {
        return \in_array($area, $this->areas, true);
    }
}
