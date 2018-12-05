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

    /** @var string */
    private $key;

    /**
     * PreparingBuildRule constructor.
     *
     * @param string $key The key of the build rule
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function applyRule(DCATDataset &$dataset, array &$data, array $defaults,
                              array $value_mappers, array $blacklist_mappers,
                              array $whitelist_mappers, array &$notices, string $prefix): void
    {
        if (!isset($data['description'])) {
            $notices[] = \sprintf(
                '%s %s: No description harvested, skipping metadata extraction',
                $prefix, $this->key
            );

            return;
        }

        if (null == $data['description'] || '' == \trim($data['description'])) {
            $notices[] = \sprintf(
                '%s %s: No description harvested, skipping metadata extraction',
                $prefix, $this->key
            );

            return;
        }

        $title  = $this->extractTitle($data['description'], $notices, $prefix);
        $themes = $this->extractThemes($data['description'], $notices, $prefix);

        if (null !== $title) {
            $data['title'] = $title;
        }

        $data['theme'] = \array_values($themes);
    }

    private function extractTitle(string &$description, array &$notices, $prefix): ?string
    {
        $notices[] = \sprintf(
            '%s %s: Attempting title metadata extraction',
            $prefix, $this->key
        );
        $starting_pattern = '[Titel:';
        $ending_pattern   = ']';

        $starting_pattern_present = \strpos($description, $starting_pattern);

        if (false === $starting_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Title starting pattern not present, skipping',
                $prefix, $this->key
            );

            return null;
        }

        $ending_pattern_present = \strpos($description, $ending_pattern, $starting_pattern_present);

        if (false === $ending_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Title closing pattern not present, skipping',
                $prefix, $this->key
            );

            return null;
        }

        $title = \substr(
            $description,
            $starting_pattern_present + \strlen($starting_pattern),
            $ending_pattern_present - ($starting_pattern_present + \strlen($starting_pattern))
        );

        if (false === $title) {
            $notices[] = \sprintf(
                '%s %s: Failed to extract title from harvested description',
                $prefix, $this->key
            );

            return null;
        }

        $description = \ltrim(
            \str_replace(
                \substr(
                    $description,
                    $starting_pattern_present,
                    $ending_pattern_present - $starting_pattern_present + 1
                ),
                '',
                $description
            )
        );

        $title = \trim($title);

        $notices[] = \sprintf(
            '%s %s: Extracted title %s from harvested description',
            $prefix, $this->key, $title
        );

        return $title;
    }

    private function extractThemes(string &$description, array &$notices, $prefix): array
    {
        $notices[] = \sprintf(
            '%s %s: Attempting theme metadata extraction',
            $prefix, $this->key
        );
        $starting_pattern = '[Thema:';
        $ending_pattern   = ']';

        $starting_pattern_present = \strpos($description, $starting_pattern);

        if (false === $starting_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Theme starting pattern not present, skipping',
                $prefix, $this->key
            );

            return [];
        }

        $ending_pattern_present = \strpos($description, $ending_pattern, $starting_pattern_present);

        if (false === $ending_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Theme closing pattern not present, skipping',
                $prefix, $this->key
            );

            return [];
        }

        $themes = \substr(
            $description,
            $starting_pattern_present + \strlen($starting_pattern),
            $ending_pattern_present - ($starting_pattern_present + \strlen($starting_pattern))
        );

        if (false === $themes) {
            $notices[] = \sprintf(
                '%s %s: Failed to extract themes from harvested description',
                $prefix, $this->key
            );

            return [];
        }

        $description = \ltrim(
            \str_replace(
                \substr(
                    $description,
                    $starting_pattern_present,
                    $ending_pattern_present - $starting_pattern_present + 1
                ),
                '',
                $description
            )
        );

        $themes = \explode(',', $themes);

        for ($i = 0; $i < \count($themes); ++$i) {
            $themes[$i] = \trim($themes[$i]);
        }

        $notices[] = \sprintf(
            '%s %s: Extracted %s theme(s) from harvested description',
            $prefix, $this->key, \count($themes)
        );

        return $themes;
    }
}
