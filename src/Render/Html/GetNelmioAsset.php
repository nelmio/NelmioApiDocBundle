<?php

/*
 * This file is part of the NelmioApiDocBundle package.
 *
 * (c) Nelmio
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Nelmio\ApiDocBundle\Render\Html;

use Symfony\Bridge\Twig\Extension\AssetExtension;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @internal
 */
class GetNelmioAsset extends AbstractExtension
{
    private AssetExtension $assetExtension;
    private string $resourcesDir;
    private string $cdnUrl;

    public function __construct(AssetExtension $assetExtension)
    {
        $this->assetExtension = $assetExtension;
        $this->cdnUrl = 'https://cdn.jsdelivr.net/gh/nelmio/NelmioApiDocBundle/public';
        $this->resourcesDir = __DIR__.'/../../../public';
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('nelmioAsset', $this, ['is_safe' => ['html']]),
        ];
    }

    public function __invoke(string $defaultAssetsMode, string $asset): string
    {
        [$extension, $mode] = $this->getExtension($defaultAssetsMode, $asset);
        [$resource, $isInline] = $this->getResource($asset, $mode);
        if ('js' == $extension) {
            return $this->renderJavascript($resource, $isInline);
        } elseif ('css' == $extension) {
            return $this->renderCss($resource, $isInline);
        } else {
            return $resource;
        }
    }

    /**
     * @return array{string, string}
     */
    private function getExtension(string $assetsMode, string $asset): array
    {
        $extension = mb_substr($asset, -3, 3, 'utf-8');
        if ('.js' === $extension) {
            return ['js', $assetsMode];
        } elseif ('png' === $extension) {
            return ['png', AssetsMode::OFFLINE == $assetsMode ? AssetsMode::CDN : $assetsMode];
        } else {
            return ['css', $assetsMode];
        }
    }

    /**
     * @return array{string, bool}
     */
    private function getResource(string $asset, string $mode): array
    {
        if (filter_var($asset, FILTER_VALIDATE_URL)) {
            return [$asset, false];
        } elseif (AssetsMode::OFFLINE === $mode) {
            return [file_get_contents($this->resourcesDir.'/'.$asset), true];
        } elseif (AssetsMode::CDN === $mode) {
            return [$this->cdnUrl.'/'.$asset, false];
        } else {
            return [$this->assetExtension->getAssetUrl(sprintf('bundles/nelmioapidoc/%s', $asset)), false];
        }
    }

    private function renderJavascript(string $script, bool $isInline): string
    {
        if ($isInline) {
            return sprintf('<script>%s</script>', $script);
        } else {
            return sprintf('<script src="%s"></script>', $script);
        }
    }

    private function renderCss(string $stylesheet, bool $isInline): string
    {
        if ($isInline) {
            return sprintf('<style>%s</style>', $stylesheet);
        } else {
            return sprintf('<link rel="stylesheet" href="%s">', $stylesheet);
        }
    }
}
