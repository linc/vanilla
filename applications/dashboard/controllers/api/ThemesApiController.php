<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use Garden\Web\Data;
use Garden\Web\Exception\ClientException;
use Garden\Web\Exception\NotFoundException;
use Garden\Web\Exception\ServerException;
use Vanilla\Theme\Asset\ThemeAsset;
use Vanilla\Theme\Theme;
use Vanilla\Theme\ThemeService;
use Vanilla\ThemingApi\Models\ThemeAssetModel;
use Vanilla\Utility\InstanceValidatorSchema;

/**
 * API Controller for the `/themes` resource.
 */
class ThemesApiController extends AbstractApiController {
    use ThemesApiSchemes;

    // Theming
    const GET_THEME_ACTION = "@@themes/GET_DONE";
    const GET_THEME_VARIABLES_ACTION = "@@themes/GET_VARIABLES_DONE";

    /** @var ThemeService */
    private $themeService;

    /**
     * ThemesApiController constructor.
     *
     * @param ThemeService $themeService
     */
    public function __construct(ThemeService $themeService) {
        $this->themeService = $themeService;
    }

    /**
     * Get a theme assets.
     *
     * @param string $themeKey The unique theme key or theme ID.
     * @param array $query
     * @return array
     */
    public function get(string $themeKey, array $query = []): array {
        $this->permission();
        $out = $this->themeResultSchema();
        $in = $this->schema([
            'allowAddonVariables:b?',
            'revisionID:i?'
        ]);
        $params = $in->validate($query);

        if (!($params['allowAddonVariables'] ?? true)) {
            $this->themeService->clearVariableProviders();
        }

        $theme = $this->themeService->getTheme($themeKey, $query);
        return $theme->jsonSerialize();
    }

    /**
     * Get a theme revisions.
     *
     * @param int $themeID The unique theme key or theme ID.
     * @return array
     */
    public function get_revisions(int $themeID): array {
        $this->permission();
        $in = $this->schema([], 'in');
        $out = $this->schema([":a" => $this->themeResultSchema()]);

        $themeRevisions = $this->themeService->getThemeRevisions($themeID);
        $result = $out->validate($themeRevisions);
        return $result;
    }

    /**
     * Get a theme assets.
     *
     * @return array
     */
    public function index(): array {
        $this->permission();
        $out = $this->schema([":a" => $this->themeResultSchema()]);

        $themes = $this->themeService->getThemes();
        $result = $out->validate($themes);
        return $result;
    }

    /**
     * Create new theme.
     *
     * @param array $body Array of incoming params.
     *        fields: name (required)
     * @return array
     */
    public function post(array $body): array {
        $this->permission("Garden.Settings.Manage");

        $in = $this->themePostSchema('in');

        $out = $this->themeResultSchema();

        $body = $in->validate($body);

        $normalizedTheme = $this->themeService->postTheme($body);

        $theme = $out->validate($normalizedTheme);
        return $theme;
    }


    /**
     * Update theme name by ID.
     *
     * @param int $themeID Theme ID
     * @param array $body Array of incoming params.
     *        fields: name (required)
     * @return array
     */
    public function patch(int $themeID, array $body): array {
        $this->permission("Garden.Settings.Manage");
        $in = $this->themePatchSchema('in');
        $out = $this->themeResultSchema();
        $body = $in->validate($body);

        $normalizedTheme = $this->themeService->patchTheme($themeID, $body);

        $theme = $out->validate($normalizedTheme);
        return $theme;
    }

    /**
     * Delete theme by ID.
     *
     * @param int $themeID Theme ID
     */
    public function delete(int $themeID) {
        $this->permission("Garden.Settings.Manage");
        $this->themeService->deleteTheme($themeID);
    }

    /**
     * Set theme as "current" theme.
     *
     * @param array $body Array of incoming params.
     *        fields: themeID (required)
     * @return array
     */
    public function put_current(array $body): array {
        $this->permission("Garden.Settings.Manage");
        $in = $this->themePutCurrentSchema('in');
        $out = $this->themeResultSchema();
        $body = $in->validate($body);

        $theme = $this->themeService->setCurrentTheme($body['themeID']);
        $theme = $out->validate($theme);
        return $theme;
    }

    /**
     * Set theme as preview theme.
     * (pseudo current theme for current session user only)
     *
     * @param array $body Array of incoming params.
     *        fields: themeID (required)
     * @return array
     */
    public function put_preview(array $body): array {
        $this->permission("Garden.Settings.Manage");
        $in = $this->themePutPreviewSchema('in');
        $out = $this->themeResultSchema();
        $body = $in->validate($body);

        $theme = $this->themeService->setPreviewTheme($body['themeID'], $body['revisionID'] ?? null);
        $theme = $out->validate($theme);
        return $theme;
    }

    /**
     * Get "current" theme.
     *
     * @return Data
     */
    public function get_current(): Data {
        $this->permission();
        $out = $this->themeResultSchema();

        $theme = $this->themeService->getCurrentTheme();
        $result = $out->validate($theme);
        return new Data($result);
    }

    /**
     * PUT theme asset (update existing or create new if asset does not exist).
     *
     * @param int $themeID The unique theme ID.
     * @param string $assetKey Unique asset key (ex: header.html, footer.html, fonts.json, styles.css)
     * @param string $body The string of body content.
     *
     * @return Data
     */
    public function put_assets(int $themeID, string $assetKey, string $body): Data {
        $this->permission("Garden.Settings.Manage");

        $theme = $this->themeService->getTheme($themeID);
        /** @var ThemeAsset $asset */
        [$asset, $assetKey, $ext] = $this->extractAssetForPath($theme, $assetKey);
        $this->themeService->setAsset($themeID, $assetKey, $body);

        return $this->get_assets($themeID, $assetKey);
    }

    /**
     * PATCH theme asset variables.json.
     *
     * @param int $themeID The unique theme ID.
     * @param string $assetKey Asset key.
     *        Note: only 'variables.json' allowed.
     * @param string $body The string of body content.
     *
     * @return Data
     */
    public function patch_assets(int $themeID, string $assetKey, string $body): Data {
        $this->permission("Garden.Settings.Manage");

        $theme = $this->themeService->getTheme($themeID);
        /** @var ThemeAsset $asset */
        [$asset, $assetKey, $ext] = $this->extractAssetForPath($theme, $assetKey);
        $this->themeService->sparseUpdateAsset($themeID, $assetKey, $body);

        return $this->get_assets($themeID, $assetKey);
    }

    /**
     * DELETE theme asset.
     *
     * @param int $themeID The unique theme ID.
     * @param string $assetKey Unique asset key (ex: header.html, footer.html, fonts.json, styles.css)
     */
    public function delete_assets(int $themeID, string $assetKey) {
        $this->permission("Garden.Settings.Manage");

        $theme = $this->themeService->getTheme($themeID);
        /** @var ThemeAsset $asset */
        [$asset, $assetKey, $ext] = $this->extractAssetForPath($theme, $assetKey);

        $this->themeService->deleteAsset($themeID, $assetKey);
    }

    /**
     * Get theme asset.
     *
     * @param string $id The unique theme key or theme ID (ex: keystone).
     * @param string $assetKey Unique asset key (ex: header, footer, fonts, styles)
     *        Note: assetKey can be filename (ex: header.html, styles.css)
     *              in that case file content returned instaed of json structure
     * @link https://github.com/vanilla/roadmap/blob/master/theming/theming-data.md#api
     *
     * @return Data
     */
    public function get_assets(string $id, string $assetKey): Data {
        $this->permission();
        $theme = $this->themeService->getTheme($id);
        /** @var ThemeAsset $asset */
        [$asset, $assetName, $ext] = $this->extractAssetForPath($theme, $assetKey);

        if ($ext) {
            if (!in_array($ext, $asset->getAllowedTypes())) {
                throw new ClientException("Invalid extension '.$ext' for asset '$assetName'.");
            }
            return $asset->render($ext);
        } else {
            $result = new Data($asset->jsonSerialize());
        }

        return $result;
    }

    /**
     * Validate an asset exists on a theme and return the asset.
     *
     * @param Theme $theme
     * @param string $assetKey
     * @return array [ThemeAsset, string $assetName, string $extension]
     */
    private function extractAssetForPath(Theme $theme, string $assetKey): array {
        $assetName = pathinfo($assetKey, PATHINFO_FILENAME);
        $ext = pathinfo($assetKey, PATHINFO_EXTENSION);

        $asset = $theme->getAssets()[$assetName] ?? null;
        if (!$asset) {
            throw new NotFoundException('Asset');
        }

        return [$asset, $assetName, $ext];
    }
}
