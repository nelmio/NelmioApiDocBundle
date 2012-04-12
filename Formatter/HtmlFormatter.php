<?php

namespace Nelmio\ApiDocBundle\Formatter;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Routing\Route;

class HtmlFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function formatOne(ApiDoc $apiDoc, Route $route)
    {
        $data = $this->getData($apiDoc, $route);
        $data['display_content'] = true;

        extract(array('content' => $this->renderOne($data)));

        ob_start();
        include __DIR__ . '/../Resources/views/formatter_resource_section.html.php';

        return $this->renderWithLayout(ob_get_clean());
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
    protected function renderResourceSection($resource, array $arrayOfData)
    {
        $content = '';
        foreach ($arrayOfData as $data) {
            $content .= $this->renderOne($data);
        }

        extract(array('content' => $content));

        ob_start();
        include __DIR__ . '/../Resources/views/formatter_resource_section.html.php';

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    protected function render(array $collection)
    {
        $content = '';
        foreach ($collection as $resource => $arrayOfData) {
            $content .= $this->renderResourceSection($resource, $arrayOfData);
        }

        return $this->renderWithLayout($content);
    }

    private function renderWithLayout($content)
    {
        extract(array('content' => $content));

        ob_start();
        include __DIR__ . '/../Resources/views/formatter_layout.html.php';

        return ob_get_clean();
    }
}
