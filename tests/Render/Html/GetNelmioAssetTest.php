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

class GetNelmioAssetTest extends WebTestCase
{
    /** @dataProvider provideAsset */
    public function test($mode, $asset, $expectedContent)
    {
        static::bootKernel();
        /** @var GetNelmioAsset $getNelmioAsset */
        $getNelmioAsset = static::getContainer()->get('nelmio_api_doc.render_docs.html.asset');

        $twigFunction = $getNelmioAsset->getFunctions()[0];

        self::assertInstanceOf(GetNelmioAsset::class, $twigFunction->getCallable());
        self::assertSame($expectedContent, $twigFunction->getCallable()->__invoke($mode, $asset));
    }

    public static function provideAsset(): iterable
    {
        $cdnDir = 'https://cdn.jsdelivr.net/gh/nelmio/NelmioApiDocBundle/public';
        $resourceDir = __DIR__.'/../../../public';

        return self::provideCss($cdnDir, $resourceDir)
            + self::provideJs($cdnDir, $resourceDir)
            + self::provideImage($cdnDir);
    }

    private static function provideCss($cdnDir, $resourceDir): array
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

    private static function provideJs($cdnDir, $resourceDir): array
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

    private static function provideImage($cdnDir): array
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
