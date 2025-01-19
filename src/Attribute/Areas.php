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
    /** @var string[] */
    private array $areas;

    /**
     * @param string[] $areas
     */
    public function __construct(array $areas)
    {
        $this->areas = [];
        foreach ($areas as $area) {
            if (!\is_string($area)) {
                throw new \InvalidArgumentException('An area must be given as a string');
            }

            if (!\in_array($area, $this->areas, true)) {
                $this->areas[] = $area;
            }
        }

        if ([] === $this->areas) {
            throw new \InvalidArgumentException('A list of areas was expected');
        }
    }

    public function has(string $area): bool
    {
        return \in_array($area, $this->areas, true);
    }
}
