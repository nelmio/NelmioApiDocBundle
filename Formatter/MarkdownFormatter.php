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

        if (isset($data['description'])) {
            $markdown .= sprintf("\n_%s_", $data['description']);
        }

        $markdown .= "\n\n";

        if (isset($data['documentation']) && !empty($data['documentation'])) {
            $markdown .= "#### Documentation ####\n\n";

            foreach (explode("\n", $data['documentation']) as $line) {
                $markdown .= "\t" . $line . "\n";
            }
        }

        if (isset($data['requirements']) && !empty($data['requirements'])) {
            $markdown .= "#### Requirements ####\n\n";

            foreach ($data['requirements'] as $name => $infos) {
                $markdown .= sprintf("**%s**\n\n", $name);

                if (!empty($infos['value'])) {
                    $markdown .= sprintf("  - Value: %s\n", $infos['value']);
                }

                if (!empty($infos['type'])) {
                    $markdown .= sprintf("  - Type: %s\n", $infos['type']);
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
                    $markdown .= sprintf("  * %s: %s\n", $key, trim(json_encode($value), '"'));
                }

                $markdown .= "\n";
            }
        }

        if (isset($data['parameters'])) {
            $markdown .= "#### Parameters ####\n\n";

            foreach ($data['parameters'] as $name => $parameter) {
                $markdown .= sprintf("%s:\n\n", $name);
                $markdown .= sprintf("  * type: %s\n", $parameter['dataType']);
                $markdown .= sprintf("  * required: %s\n", $parameter['required'] ? 'true' : 'false');

                if (isset($parameter['description']) && !empty($parameter['description'])) {
                    $markdown .= sprintf("  * description: %s\n", $parameter['description']);
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
        foreach ($collection as $resource => $arrayOfData) {
            $markdown .= $this->renderResourceSection($resource, $arrayOfData);
            $markdown .= "\n";
        }

        return trim($markdown);
    }

    private function renderResourceSection($resource, array $arrayOfData)
    {
        $markdown = sprintf("# %s #\n\n", $resource);

        foreach ($arrayOfData as $data) {
            $markdown .= $this->renderOne($data);
            $markdown .= "\n";
        }

        return $markdown;
    }
}
