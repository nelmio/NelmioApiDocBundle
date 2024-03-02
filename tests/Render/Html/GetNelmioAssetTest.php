<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Tests\Render\Html;

use Nelmio\ApiDocBundle\Render\Html\AssetsMode;
use Nelmio\ApiDocBundle\Render\Html\GetNelmioAsset;
use Nelmio\ApiDocBundle\Tests\Functional\WebTestCase;
use Twig\TwigFunction;

class GetNelmioAssetTest extends WebTestCase
{
    /** @dataProvider provideAsset */
    public function test($mode, $asset, $expectedContent)
    {
        static::bootKernel();
        /** @var GetNelmioAsset $getNelmioAsset */
        $getNelmioAsset = static::getContainer()->get('nelmio_api_doc.render_docs.html.asset');
        /** @var TwigFunction */
        $twigFunction = $getNelmioAsset->getFunctions()[0];
        self::assertSame($expectedContent, $twigFunction->getCallable()->__invoke($mode, $asset));
    }

    public function provideAsset()
    {
        $cdnDir = 'https://cdn.jsdelivr.net/gh/nelmio/NelmioApiDocBundle/public';
        $resourceDir = __DIR__.'/../../../public';

        return $this->provideCss($cdnDir, $resourceDir)
            + $this->provideJs($cdnDir, $resourceDir)
            + $this->provideImage($cdnDir);
    }

    private function provideCss($cdnDir, $resourceDir)
    {
        return [
            'bundled css' => [
                AssetsMode::BUNDLE,
                'style.css',
                '<link rel="stylesheet" href="/bundles/nelmioapidoc/style.css">',
            ],
            'cdn css' => [
                AssetsMode::CDN,
                'style.css',
                '<link rel="stylesheet" href="'.$cdnDir.'/style.css">',
            ],
            'offline css' => [
                AssetsMode::OFFLINE,
                'style.css',
                '<style>'.file_get_contents($resourceDir.'/style.css').'</style>',
            ],
            'external css' => [
                AssetsMode::BUNDLE,
                'https://cdn.com/my.css',
                '<link rel="stylesheet" href="https://cdn.com/my.css">',
            ],
        ];
    }

    private function provideJs($cdnDir, $resourceDir)
    {
        return [
            'bundled js' => [
                AssetsMode::BUNDLE,
                'init-swagger-ui.js',
                '<script src="/bundles/nelmioapidoc/init-swagger-ui.js"></script>',
            ],
            'cdn js' => [
                AssetsMode::CDN,
                'init-swagger-ui.js',
                '<script src="'.$cdnDir.'/init-swagger-ui.js"></script>',
            ],
            'offline js' => [
                AssetsMode::OFFLINE,
                'init-swagger-ui.js',
                '<script>'.file_get_contents($resourceDir.'/init-swagger-ui.js').'</script>',
            ],
            'external js' => [
                AssetsMode::BUNDLE,
                'https://cdn.com/my.js',
                '<script src="https://cdn.com/my.js"></script>',
            ],
        ];
    }

    private function provideImage($cdnDir)
    {
        return [
            'bundled image' => [
                AssetsMode::BUNDLE,
                'logo.png',
                '/bundles/nelmioapidoc/logo.png',
            ],
            'cdn image' => [
                AssetsMode::CDN,
                'logo.png',
                $cdnDir.'/logo.png',
            ],
            'offline image fallbacks to cdn' => [
                AssetsMode::OFFLINE,
                'logo.png',
                $cdnDir.'/logo.png',
            ],
        ];
    }
}
