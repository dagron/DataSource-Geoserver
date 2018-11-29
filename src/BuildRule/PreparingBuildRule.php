<?php

namespace NijmegenSync\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use NijmegenSync\Dataset\Builder\IDatasetBuildRule;

/**
 * Class PreparingBuildRule.
 *
 * This build rule is responsible for extracting the theme information from the harvested
 * description prior to the execution of the build rules for the description and theme properties.
 *
 * After this build rule has finished the themes found in the description will be removed from said
 * description and will be added to the 'theme' key of the $data array.
 */
class PreparingBuildRule implements IDatasetBuildRule
{
    /** @var string */
    private static $START_PATTERN = '[Thema:';

    /** @var string */
    private static $END_PATTERN = ']';

    /**
     * {@inheritdoc}
     */
    public function applyRule(DCATDataset &$dataset, array &$data, array $defaults,
                              array $value_mappers, array $blacklist_mappers,
                              array $whitelist_mappers, array &$notices): void
    {
        if (!isset($data['description'])) {
            return;
        }

        $description = $data['description'];

        if (null == $description || '' == \trim($description)) {
            return;
        }

        if (\substr($description, 0, \strlen(self::$START_PATTERN)) !== self::$START_PATTERN) {
            return;
        }

        if (false === \strpos($description, ']')) {
            $notices[] = 'could not extract themes from harvested description, no ] character found';

            return;
        }

        $extracted_themes = \explode(
            ',',
            \substr(
                $description,
                \strlen(self::$START_PATTERN),
                \strlen($description) - \strpos($description, self::$END_PATTERN) + 1
            )
        );
        $data['description'] = \substr(
            $description, \strpos($description, self::$END_PATTERN) + 1
        );

        foreach ($extracted_themes as $theme) {
            $data['theme'][] = \ltrim(\rtrim($theme));
        }

        $notices[] = \sprintf(
            'extracted %d themes from harvested description', \count($data['theme'])
        );
    }
}
