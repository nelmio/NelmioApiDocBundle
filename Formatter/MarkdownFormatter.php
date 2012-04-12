<?php

namespace Nelmio\ApiBundle\Formatter;

class MarkdownFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        $markdown = sprintf("### `%s` %s ###\n", $data['method'], $data['uri']);

        if (isset($data['comment'])) {
            $markdown .= sprintf("\n_%s_", $data['comment']);
        }

        $markdown .= "\n\n";

        if (isset($data['requirements']) && !empty($data['requirements'])) {
            $markdown .= "#### Requirements ####\n\n";

            foreach ($data['requirements'] as $name => $value) {
                $markdown .= sprintf("* %s: %s\n", $name, $value);
            }

            $markdown .= "\n";
        }

        if (isset($data['filters'])) {
            $markdown .= "#### Filters ####\n\n";

            foreach ($data['filters'] as $name => $filter) {
                $markdown .= sprintf("%s:\n\n", $name);

                foreach ($filter as $key => $value) {
                    $markdown .= sprintf("  * %s: %s\n", $key, $value);
                }

                $markdown .= "\n";
            }
        }

        if (isset($data['parameters'])) {
            $markdown .= "#### Parameters ####\n\n";

            foreach ($data['parameters'] as $name => $parameter) {
                $markdown .= sprintf("%s:\n\n", $name);
                $markdown .= sprintf("  * type: %s\n", $parameter['type']);
                $markdown .= sprintf("  * is_required: %s\n", $parameter['is_required'] ? 'true' : 'false');
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
        foreach ($collection as $data) {
            $markdown .= $this->renderOne($data);
            $markdown .= "\n";
        }

        return $markdown;
    }
}
