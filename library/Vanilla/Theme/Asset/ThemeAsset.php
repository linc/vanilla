<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme\Asset;

 use Garden\Web\Data;

 /**
  * Basic theme asset.
  */
abstract class ThemeAsset implements \JsonSerializable {

    /**
     * Get the type of the asset.
     *
     * @return string
     */
    abstract public function getDefaultType(): string;

    /**
     * Get the HTTP content-type of the asset.
     *
     * @return string
     */
    abstract public function getContentType(): string;

    /**
     * Get the value of the asset.
     *
     * This may be an array, string, boolean, etc.
     *
     * @return mixed
     */
    abstract public function getValue();

    /**
     * Serialize the asset value to a string.
     *
     * @return string
     */
    abstract public function __toString(): string;

    /**
     * Get all allowed types for the asset.
     *
     * @return array
     */
    public function getAllowedTypes(): array {
        return [$this->getDefaultType()];
    }

    /**
     * Render the asset.
     *
     * @param string $asType The type to render the asset as.
     *
     * @return Data
     */
    public function render(string $asType = null): Data {
        $result = new Data($this->__toString());
        $result->setHeader('Content-Type', $this->getContentType());
        return $result;
    }

    /**
     * Represent the asset as an array.
     *
     * @return array
     */
    public function asArray(): array {
        return [
            'data' => $this->getValue(),
            'type' => $this->getDefaultType(),
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize() {
        return $this->asArray();
    }
}
