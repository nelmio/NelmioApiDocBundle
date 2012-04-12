<?php

namespace Nelmio\ApiBundle\Formatter;

use Nelmio\ApiBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

class HtmlFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $apiDoc, Route $route)
    {
        return $this->renderWithLayout(parent::formatOne($apiDoc, $route));
    }

    /**
     * {@inheritdoc}
     */
    protected function renderOne(array $data)
    {
        extract($data);

        ob_start();
        include __DIR__ . '/../Resources/views/formatter.html.php';

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        $content = '';
        foreach ($collection as $data) {
            $content .= $this->renderOne($data);
        }

        return $this->renderWithLayout($content);
    }

    private function renderWithLayout($content)
    {
        $array = array('content' => $content);
        extract($array);

        ob_start();
        include __DIR__ . '/../Resources/views/formatter_layout.html.php';

        return ob_get_clean();
    }
}
