<?php
/**
 * @author Adam Charron <adam.c@vanillaforums.com>
 * @copyright 2009-2020 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme;

use Nette\Utils\Json;
use Vanilla\Contracts\ConfigurationInterface;
use Vanilla\Theme\Asset\CssThemeAsset;
use Vanilla\Theme\Asset\HtmlThemeAsset;
use Vanilla\Theme\Asset\ImageThemeAsset;
use Vanilla\Theme\Asset\JavascriptThemeAsset;
use Vanilla\Theme\Asset\JsonThemeAsset;
use Vanilla\Theme\Asset\NeonThemeAsset;
use Vanilla\Theme\Asset\ThemeAsset;
use Vanilla\Theme\Asset\TwigThemeAsset;
use Vanilla\Web\Asset\DeploymentCacheBuster;

/**
 * Factory for creating theme assets.
 */
class ThemeAssetFactory {

    const ASSET_TYPE_HTML = "html";
    const ASSET_TYPE_JSON = "json";
    const ASSET_TYPE_CSS = "css";
    const ASSET_TYPE_JS = "js";
    const ASSET_TYPE_NEON = "neon";
    const ASSET_TYPE_TWIG = "twig";

    const ASSET_HEADER = 'header';
    const ASSET_FOOTER = 'footer';
    const ASSET_VARIABLES = 'variables';
    const ASSET_FONTS = 'fonts';
    const ASSET_SCRIPTS = 'scripts';
    const ASSET_STYLES = 'styles';
    const ASSET_JAVASCRIPT = 'javascript';

    const DEFAULT_ASSETS = [
        self::ASSET_HEADER => [
            "type" => self::ASSET_TYPE_HTML,
            "file" => "header.html",
            "default" => "",
        ],
        self::ASSET_FOOTER => [
            "type" => self::ASSET_TYPE_HTML,
            "file" => "footer.html",
            "default" => "",
        ],
        self::ASSET_VARIABLES => [
            "type" => self::ASSET_TYPE_JSON,
            "file" => "variables.json",
            "default" => "{}",
        ],
        self::ASSET_FONTS => [
            "type" => self::ASSET_TYPE_JSON,
            "file" => "fonts.json",
            "default" => "[]",
        ],
        self::ASSET_SCRIPTS => [
            "type" => self::ASSET_TYPE_JSON,
            "file" => "scripts.json",
            "default" => "[]",
        ],
        self::ASSET_STYLES => [
            "type" => self::ASSET_TYPE_CSS,
            "file" => "styles.css",
            "default" => "",
        ],
        self::ASSET_JAVASCRIPT => [
            "type" => self::ASSET_TYPE_JS,
            "file" => "javascript.js",
            "default" => "",
        ],
    ];

    /** @var \Gdn_Request */
    private $request;

    /** @var DeploymentCacheBuster */
    private $cacheBuster;

    /** @var ConfigurationInterface */
    private $config;

    /**
     * DI.
     *
     * @param \Gdn_Request $request
     * @param DeploymentCacheBuster $cacheBuster
     * @param ConfigurationInterface $config
     */
    public function __construct(\Gdn_Request $request, DeploymentCacheBuster $cacheBuster, ConfigurationInterface $config) {
        $this->request = $request;
        $this->cacheBuster = $cacheBuster;
        $this->config = $config;
    }

    /**
     * @return ThemeAssetFactory
     */
    public static function instance(): ThemeAssetFactory {
        return \Gdn::getContainer()->get(ThemeAssetFactory::class);
    }

    /**
     * Create an asset.
     *
     * @param Theme $theme The theme the asset is being created for.
     * @param string $assetType The ASSET_TYPE of the asset.
     * @param string $assetName The name of the asset.
     * @param string $assetContents The contents of the asset.
     * @return ThemeAsset|null
     */
    public function createAsset(Theme $theme, string $assetType, string $assetName, string $assetContents): ?ThemeAsset {
        $asset = null;
        $themeID = $theme->getThemeID();
        $buster = $this->getThemeAssetCacheBuster($theme);
        $url = $this->request->getSimpleUrl("/api/v2/$themeID/$assetName.$assetType?v=$buster");

        switch ($assetType) {
            case self::ASSET_TYPE_HTML:
                return new HtmlThemeAsset($assetContents);
            case self::ASSET_TYPE_TWIG:
                return new TwigThemeAsset($assetContents);
            case self::ASSET_TYPE_JSON:
                return new JsonThemeAsset($assetContents);
            case self::ASSET_TYPE_NEON:
                return new NeonThemeAsset($assetContents);
            case self::ASSET_TYPE_JS:
                return new JavascriptThemeAsset($assetContents, $url);
            case self::ASSET_TYPE_CSS:
                return new CssThemeAsset($assetContents, $url);
            default:
                return null;
        }
    }

    /**
     * Get an array of logo assets for a theme.
     *
     * @param JsonThemeAsset $variables The variables to check.
     *
     * @return array
     */
    public function getLogoAssets(?JsonThemeAsset $variables = null): array {
        $logoAssets = [];
        $desktopLogoUrl = null;
        $mobileLogoUrl = null;
        if ($variables) {
            $desktopLogoUrl = $variables->get('titleBar.logo.desktop.url', null) ?: null;
            $mobileLogoUrl = $variables->get('titleBar.logo.mobile.url', $desktopLogoUrl) ?: null;
        }

        if ($desktopLogoUrl === null) {
            // Check the config.
            $desktopConfig = $this->config->get('Garden.Logo') ?: null;
            $mobileConfig = $this->config->get('Garden.MobileLogo', $desktopConfig) ?: null;

            if ($desktopConfig) {
                $desktopLogoUrl = \Gdn_Upload::url($desktopConfig);
            }

            if ($mobileConfig) {
                $mobileLogoUrl = \Gdn_Upload::url($mobileConfig);
            }
        }

        if ($desktopLogoUrl) {
            $logoAssets['logo'] = new ImageThemeAsset($desktopLogoUrl);
        }

        if ($mobileLogoUrl) {
            $logoAssets['mobileLogo'] = new ImageThemeAsset($mobileLogoUrl);
        }

        return $logoAssets;
    }

    /**
     * Get a cache buster for some theme assets.
     *
     * @param Theme $theme
     * @return string
     */
    private function getThemeAssetCacheBuster(Theme $theme): string {
        $themeBuster = $theme->getVersion();
        $deployBuster = $this->cacheBuster->value();
        return "$themeBuster-$deployBuster";
    }
}
