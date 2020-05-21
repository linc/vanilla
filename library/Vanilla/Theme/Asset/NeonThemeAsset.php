<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme\Asset;

use Garden\Web\Data;
use Nette\Neon\Neon;
use Vanilla\Theme\ThemeAssetFactory;

/**
 * JSON theme asset.
 */
class NeonThemeAsset extends JsonThemeAsset {

    /** @var string */
    protected $neonString;

    /**
     * Configure the JSON asset.
     *
     * @param string $data
     */
    public function __construct($data) {
        $this->neonString = $data;
        $this->data = Neon::decode($data);
        $this->jsonString = json_encode($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string {
        return "text/neon";
    }

    /**
     * @inheritdoc
     */
    public function getAllowedTypes(): array {
        return [ThemeAssetFactory::ASSET_TYPE_JSON, ThemeAssetFactory::ASSET_TYPE_NEON];
    }

    /**
     * Render the asset.
     *
     * @param string $asType The type to render the asset as.
     *
     * @return Data
     */
    public function render(string $asType = null): Data {
        switch ($asType) {
            case ThemeAssetFactory::ASSET_TYPE_NEON:
                $data = new Data($this->neonString);
                $data->setHeader('Content-Type', $this->getContentType());
                return $data;
            case ThemeAssetFactory::ASSET_TYPE_JSON:
            default:
                $data = new Data($this->jsonString);
                $data->setHeader('Content-Type', parent::getContentType());
                return $data;
        }
    }
}
