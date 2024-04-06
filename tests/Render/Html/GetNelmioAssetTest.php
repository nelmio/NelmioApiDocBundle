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
    private const CDN_DIR = 'https://cdn.jsdelivr.net/gh/nelmio/NelmioApiDocBundle/public';
    private const RESOURCE_DIR = __DIR__.'/../../../public';

    /**
     * @dataProvider provideCss
     * @dataProvider provideJs
     * @dataProvider provideImage
     */
    public function test(string $mode, string $asset, string $expectedContent): void
    {
        static::bootKernel();
        /** @var GetNelmioAsset $getNelmioAsset */
        $getNelmioAsset = static::getContainer()->get('nelmio_api_doc.render_docs.html.asset');

        $twigFunction = $getNelmioAsset->getFunctions()[0];

        self::assertInstanceOf(GetNelmioAsset::class, $twigFunction->getCallable());
        self::assertSame($expectedContent, $twigFunction->getCallable()->__invoke($mode, $asset));
    }

    public static function provideCss(): \Generator
    {
        yield 'bundled css' => [
            AssetsMode::BUNDLE,
            'style.css',
            '<link rel="stylesheet" href="/bundles/nelmioapidoc/style.css">',
        ];
        yield 'cdn css' => [
            AssetsMode::CDN,
            'style.css',
            '<link rel="stylesheet" href="'.self::CDN_DIR.'/style.css">',
        ];
        yield 'offline css' => [
            AssetsMode::OFFLINE,
            'style.css',
            '<style>'.file_get_contents(self::RESOURCE_DIR.'/style.css').'</style>',
        ];
        yield 'external css' => [
            AssetsMode::BUNDLE,
            'https://cdn.com/my.css',
            '<link rel="stylesheet" href="https://cdn.com/my.css">',
        ];
    }

    public static function provideJs(): \Generator
    {
        yield 'bundled js' => [
            AssetsMode::BUNDLE,
            'init-swagger-ui.js',
            '<script src="/bundles/nelmioapidoc/init-swagger-ui.js"></script>',
        ];
        yield 'cdn js' => [
            AssetsMode::CDN,
            'init-swagger-ui.js',
            '<script src="'.self::CDN_DIR.'/init-swagger-ui.js"></script>',
        ];
        yield 'offline js' => [
            AssetsMode::OFFLINE,
            'init-swagger-ui.js',
            '<script>'.file_get_contents(self::RESOURCE_DIR.'/init-swagger-ui.js').'</script>',
        ];
        yield 'external js' => [
            AssetsMode::BUNDLE,
            'https://cdn.com/my.js',
            '<script src="https://cdn.com/my.js"></script>',
        ];
    }

    public static function provideImage(): \Generator
    {
        yield 'bundled image' => [
            AssetsMode::BUNDLE,
            'logo.png',
            '/bundles/nelmioapidoc/logo.png',
        ];
        yield 'cdn image' => [
            AssetsMode::CDN,
            'logo.png',
            self::CDN_DIR.'/logo.png',
        ];
        yield 'offline image fallbacks to cdn' => [
            AssetsMode::OFFLINE,
            'logo.png',
            self::CDN_DIR.'/logo.png',
        ];
    }
}
