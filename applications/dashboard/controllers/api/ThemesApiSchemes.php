<?php
/**
 * @copyright 2009-2019 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */

use Garden\Schema\Schema;
use Vanilla\Theme\Theme;
use Vanilla\Theme\Asset;
use Vanilla\Utility\InstanceValidatorSchema;
use Vanilla\Theme\ThemeService;

/**
 * ThemesApiController schemes.
 */
trait ThemesApiSchemes {

    private $assetArrayJsonSchema;

    private $assetArrayStringSchema;


    /**
     * Result theme schema
     *
     * @return Schema
     */
    private function themeResultSchema(): Schema {
        return new InstanceValidatorSchema(Theme::class);
    }

    /**
     * Get 'assets' schema
     *
     * @return Schema
     */
    private function assetsSchema(): Schema {
        $schema = Schema::parse([
            "header?" => new InstanceValidatorSchema([Asset\HtmlThemeAsset::class, Asset\TwigThemeAsset::class]),
            "footer?" => new InstanceValidatorSchema([Asset\HtmlThemeAsset::class, Asset\TwigThemeAsset::class]),
            "variables?" => new InstanceValidatorSchema(Asset\JsonThemeAsset::class),
            "fonts?" => new InstanceValidatorSchema(Asset\JsonThemeAsset::class),
            "scripts?" => new InstanceValidatorSchema(Asset\JsonThemeAsset::class),
            "styles:s?",
            "javascript:s?",
            "logo?" => new InstanceValidatorSchema(Asset\ImageThemeAsset::class),
            "mobileLogo?" => new InstanceValidatorSchema(Asset\ImageThemeAsset::class),
        ])->setID('themeAssetsSchema');
        return $schema;
    }

    /**
     * POST theme schema
     *
     * @param string $type
     * @return Schema
     */
    private function themePostSchema(string $type = 'in'): Schema {
        $schema = $this->schema(
            Schema::parse([
                'name:s' => [
                    'description' => 'Custom theme name.',
                ],
                'parentTheme:s' => [
                    'description' => 'Parent theme template name.',
                ],
                'parentVersion:s' => [
                    'description' => 'Parent theme template version/revision.',
                ],
                'assets?' => Schema::parse([
                    "header?" => $this->assetsPutArraySchema(),
                    "footer?" => $this->assetsPutArraySchema(),
                    "variables?" => $this->assetsPutArraySchema(),
                    "fonts:s?",
                    "scripts:s?",
                    "styles:s?",
                    "javascript:s?"
                ])
                    ->addValidator('header', [ThemeService::class, 'validator'])
                    ->addValidator('footer', [ThemeService::class, 'validator'])
                    ->addValidator('variables', [ThemeService::class, 'validator'])
                    ->addValidator('fonts', [ThemeService::class, 'validator'])
                    ->addValidator('scripts', [ThemeService::class, 'validator'])
            ]),
            $type
        );
        return $schema;
    }

    /**
     * PATCH theme schema
     *
     * @param string $type
     * @return Schema
     */
    private function themePatchSchema(string $type = 'in'): Schema {
        $schema = $this->schema(
            Schema::parse([
                'name:s?' => [
                    'description' => 'Custom theme name.',
                ],
                'parentTheme:s?' => [
                    'description' => 'Parent theme template name.',
                ],
                'parentVersion:s?' => [
                    'description' => 'Parent theme template version/revision.',
                ],
                'revisionID:i?' => [
                    'description' => 'Theme revision ID.',
                ],
                'revisionName:s?' => [
                    'description' => 'Theme revision name.',
                ],
                'assets?' => Schema::parse([
                    "header?" => $this->assetsPutArraySchema(),
                    "footer?" => $this->assetsPutArraySchema(),
                    "variables?" => $this->assetsPutArraySchema(),
                    "fonts:s?",
                    "scripts:s?",
                    "styles:s?",
                    "javascript:s?"
                ])
                    ->addValidator('header', [ThemeService::class, 'validator'])
                    ->addValidator('footer', [ThemeService::class, 'validator'])
                    ->addValidator('variables', [ThemeService::class, 'validator'])
                    ->addValidator('fonts', [ThemeService::class, 'validator'])
                    ->addValidator('scripts', [ThemeService::class, 'validator'])
            ]),
            $type
        );
        return $schema;
    }

    /**
     *
     */
    public function assetArrayStringSchema(): Schema {
        if (!$this->assetArrayJsonSchema) {
            $this->assetArrayJsonSchema = $this->schema([
                'data:s',
                'type:s',
            ]);
        }

        return $this->assetArrayJsonSchema;
    }

    /**
     *
     */
    public function assetArrayJsonSchema(): Schema {
        if (!$this->assetArrayJsonSchema) {
            $this->assetArrayJsonSchema = $this->schema([
                'data:o',
                'type:s',
            ]);
        }

        return $this->assetArrayJsonSchema;
    }

    /**
     * PUT current theme schema
     *
     * @param string $type
     * @return Schema
     */
    private function themePutCurrentSchema(string $type = 'in'): Schema {
        $schema = $this->schema(
            Schema::parse([
                'themeID:s' => [
                    'description' => 'Theme ID or Theme Key',
                ],
            ]),
            $type
        );
        return $schema;
    }

    /**
     * PUT preview theme schema
     *
     * @param string $type
     * @return Schema
     */
    private function themePutPreviewSchema(string $type = 'in'): Schema {
        $schema = $this->schema(
            Schema::parse([
                'themeID:s?' => [
                    'description' => 'Theme ID or Theme Key',
                ],
                'revisionID:i?' => [
                    'description' => 'Theme revision ID',
                ],
            ]),
            $type
        );
        return $schema;
    }
}
