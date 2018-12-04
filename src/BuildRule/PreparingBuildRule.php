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
                              array $whitelist_mappers, array &$notices, string $prefix): void
    {
        if (!isset($data['description'])) {
            $notices[] = \sprintf('%s No description harvested, skipping theme extraction', $prefix);

            return;
        }

        $description = $data['description'];

        if (null == $description || '' == \trim($description)) {
            $notices[] = \sprintf('%s No description harvested, skipping theme extraction', $prefix);

            return;
        }

        if (\substr($description, 0, \strlen(self::$START_PATTERN)) !== self::$START_PATTERN) {
            $notices[] = \sprintf('%s Harvested description does not contain Theme pattern, skipping theme extraction', $prefix);

            return;
        }

        if (false === \strpos($description, ']')) {
            $notices[] = \sprintf('%s Could not extract themes from harvested description, no closing pattern found', $prefix);

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
            '%s Extracted %d themes from harvested description', $prefix, \count($data['theme'])
        );
    }
}
