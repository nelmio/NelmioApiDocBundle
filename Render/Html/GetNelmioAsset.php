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

class GetNelmioAsset
{
    private $assetExtension;
    private $defaultAssetsMode;

    public function __construct(AssetExtension $assetExtension, $defaultAssetsMode)
    {
        $this->assetExtension = $assetExtension;
        $this->defaultAssetsMode = $defaultAssetsMode;
    }

    public function __invoke($asset, $forcedMode = null)
    {
        $mode = $forcedMode ?: $this->defaultAssetsMode;
        if (AssetsMode::CDN === $mode) {
            return sprintf(
                'https://cdn.jsdelivr.net/gh/nelmio/NelmioApiDocBundle@4.1/Resources/public/%s',
                $asset
            );
        } elseif (AssetsMode::OFFLINE === $mode) {
            return file_get_contents(__DIR__.sprintf('/../../Resources/public/%s', $asset));
        } else {
            return $this->assetExtension->getAssetUrl(sprintf('bundles/nelmioapidoc/%s', $asset));
        }
    }
}
