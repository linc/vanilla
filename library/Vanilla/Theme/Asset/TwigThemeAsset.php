<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

namespace Vanilla\Theme\Asset;

use Vanilla\Theme\ThemeAssetFactory;
use Vanilla\Web\TwigRenderTrait;

 /**
  * HTML theme asset.
  */
class TwigThemeAsset extends HtmlThemeAsset {

    use TwigRenderTrait;

    /** @var string Twig Template content of this asset. */
    private $template = "";

    /** @var string Type of asset. */
    protected $type = "html";

    /**
     * Configure the HTML asset.
     *
     * @param string $template
     */
    public function __construct(string $template) {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getDefaultType(): string {
        return ThemeAssetFactory::ASSET_TYPE_TWIG;
    }

    /**
     * Represent the HTML asset as an array.
     *
     * @return array
     */
    public function asArray(): array {
        return [
            "data" => $this->renderHtml([]),
            "template" => $this->template,
            "type" => $this->type,
        ];
    }

    /**
     * Render the template as HTML.
     *
     * @param array $data The data to render with.
     *
     * @return string
     */
    public function renderHtml(array $data = []): string {
        return $this->renderTwigFromString($this->getTemplate(), $data);
    }

    /**
     * Get the HTML content.
     *
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * Return the HTML string content of the asset.
     *
     * @return string
     */
    public function __toString(): string {
        return $this->getTemplate();
    }
}
