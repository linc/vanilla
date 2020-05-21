<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme\Asset;

use Vanilla\Theme\ThemeAssetFactory;

 /**
  * JSON theme asset.
  */
class JsonThemeAsset extends ThemeAsset {

    /** @var string JSON content of this asset. */
    protected $jsonString;

    /** @var array */
    protected $data;

    /**
     * Configure the JSON asset.
     *
     * @param string $data
     */
    public function __construct(string $data) {
        $this->jsonString = $data;
        $this->data = json_decode($data, true);
    }

    /**
     * @inheritdoc
     */
    public function getDefaultType(): string {
        return ThemeAssetFactory::ASSET_TYPE_JSON;
    }

    /**
     * @inheritdoc
     */
    public function getContentType(): string {
        return "application/json";
    }

    /**
     * @inheritdoc
     */
    public function getValue(): array {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function __toString(): string {
        return $this->jsonString;
    }

    /**
     * Pull a value out of the json.
     *
     * @param string $key A key in dot notation.
     * @param mixed $default The default value.
     * @return mixed
     */
    public function get(string $key, $default) {
        return valr($key, $default);
    }
}
