<?php

/*
 * This file is part of the NelmioApiDocBundle.
 *
 * (c) Nelmio <hello@nelm.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Formatter;

class MarkdownFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        $markdown = sprintf("### `%s` %s ###\n", $data['method'], $data['uri']);

        if (isset($data['deprecated']) && false !== $data['deprecated']) {
            $markdown .= "### This method is deprecated ###";
            $markdown .= "\n\n";
        }

        if (isset($data['description'])) {
            $markdown .= sprintf("\n_%s_", $data['description']);
        }

        $markdown .= "\n\n";

        if (isset($data['documentation']) && !empty($data['documentation'])) {
            if (isset($data['description']) && 0 === strcmp($data['description'], $data['documentation'])) {
                $markdown .= $data['documentation'];
                $markdown .= "\n\n";
            }
        }

        if (isset($data['requirements']) && !empty($data['requirements'])) {
            $markdown .= "#### Requirements ####\n\n";

            foreach ($data['requirements'] as $name => $infos) {
                $markdown .= sprintf("**%s**\n\n", $name);

                if (!empty($infos['requirement'])) {
                    $markdown .= sprintf("  - Requirement: %s\n", $infos['requirement']);
                }

                if (!empty($infos['dataType'])) {
                    $markdown .= sprintf("  - Type: %s\n", $infos['dataType']);
                }

                if (!empty($infos['description'])) {
                    $markdown .= sprintf("  - Description: %s\n", $infos['description']);
                }
            }

            $markdown .= "\n";
        }

        if (isset($data['filters'])) {
            $markdown .= "#### Filters ####\n\n";

            foreach ($data['filters'] as $name => $filter) {
                $markdown .= sprintf("%s:\n\n", $name);

                foreach ($filter as $key => $value) {
                    $markdown .= sprintf("  * %s: %s\n", ucwords($key), trim(str_replace('\\\\', '\\', json_encode($value)), '"'));
                }

                $markdown .= "\n";
            }
        }

        if (isset($data['parameters'])) {
            $markdown .= "#### Parameters ####\n\n";

            foreach ($data['parameters'] as $name => $parameter) {
                if (!$parameter['readonly']) {
                    $markdown .= sprintf("%s:\n\n", $name);
                    $markdown .= sprintf("  * type: %s\n", $parameter['dataType']);
                    $markdown .= sprintf("  * required: %s\n", $parameter['required'] ? 'true' : 'false');

                    if (isset($parameter['description']) && !empty($parameter['description'])) {
                        $markdown .= sprintf("  * description: %s\n", $parameter['description']);
                    }
                    if (isset($parameter['default']) && !empty($parameter['default'])) {
                        $markdown .= sprintf("  * default value: %s\n", $parameter['default']);
                    }

                    $markdown .= "\n";
                }
            }
        }

        if (isset($data['response'])) {
            $markdown .= "#### Response ####\n\n";

            foreach ($data['response'] as $name => $parameter) {
                $markdown .= sprintf("%s:\n\n", $name);
                $markdown .= sprintf("  * type: %s\n", $parameter['dataType']);

                if (isset($parameter['description']) && !empty($parameter['description'])) {
                    $markdown .= sprintf("  * description: %s\n", $parameter['description']);
                }

                if (null !== $parameter['sinceVersion'] || null !== $parameter['untilVersion']) {
                    $markdown .= "  * versions: ";
                    if ($parameter['sinceVersion']) {
                        $markdown .= '>='.$parameter['sinceVersion'];
                    }
                    if ($parameter['untilVersion']) {
                        if ($parameter['sinceVersion']) {
                            $markdown .= ',';
                        }
                        $markdown .= '<='.$parameter['untilVersion'];
                    }
                    $markdown .= "\n";
                }

                $markdown .= "\n";
            }
        }

        return $markdown;
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        $markdown = '';
        foreach ($collection as $section => $resources) {
            $markdown .= $this->renderResourceSection($section, $resources);
            $markdown .= "\n";
        }

        return trim($markdown);
    }

    private function renderResourceSection($section, array $resources)
    {
        if ('_others' !== $section) {
            $markdown = sprintf("# %s #\n\n", $section);
        } else {
            $markdown = '';
        }

        foreach ($resources as $resource => $methods) {
            if ('_others' === $section && 'others' !== $resource) {
                $markdown .= sprintf("## %s ##\n\n", $resource);
            } elseif ('others' !== $resource) {
                $markdown .= sprintf("## %s ##\n\n", $resource);
            }

            foreach ($methods as $method) {
                $markdown .= $this->renderOne($method);
                $markdown .= "\n";
            }
        }

        return $markdown;
    }
}
