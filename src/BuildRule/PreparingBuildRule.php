<?php

namespace NijmegenSync\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use NijmegenSync\Dataset\Builder\IDatasetBuildRule;
use NijmegenSync\Exception\AbortDatasetBuilderException;

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
    private $property;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $property)
    {
        $this->property = $property;
    }

    /**
     * {@inheritdoc}
     */
    public function getProperty(): string
    {
        return $this->property;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AbortDatasetBuilderException Thrown if the metadata suggests that the dataset should
     *                                      not be harvested
     */
    public function applyRule(DCATDataset &$dataset, array &$data, array $defaults,
                              array $value_mappers, array $blacklist_mappers,
                              array $whitelist_mappers, array &$notices, string $prefix): void
    {
        if (!isset($data['description'])) {
            throw new AbortDatasetBuilderException(
                'metadata indicates the dataset should not be harvested as no description is present'
            );
        }

        if (null == $data['description'] || '' == \trim($data['description'])) {
            throw new AbortDatasetBuilderException(
                'metadata indicates the dataset should not be harvested as no description is present'
            );
        }

        $should_synchronize = $this->extractSynchronization(
            $data['description'], $notices, $prefix
        );

        if (null === $should_synchronize) {
            throw new AbortDatasetBuilderException(
                'metadata indicates dataset should not be harvested as the \'Dataset delen\' vocabulary is absent'
            );
        }

        if (!$should_synchronize) {
            throw new AbortDatasetBuilderException(
                'metadata indicates dataset should not be harvested'
            );
        }

        $data['title']       = $this->extractTitle(
            $data['description'], $notices, $prefix
        );
        $data['description'] = $this->extractDescription(
            $data['description'], $notices, $prefix, $data['title']
        );
        $data['theme']       = $this->extractThemes(
            $data['description'], $notices, $prefix
        );
        $data['highValue']   = $this->extractHighValue(
            $data['description'], $notices, $prefix
        );
    }

    /**
     * Attempts to extract metadata from the given description text based on the patterns provided.
     *
     * @param string $description         The text to extract the metadata from
     * @param string $property_to_extract The name of the metadata property to extract
     * @param string $starting_pattern    The starting pattern identifying the metadata
     * @param string $closing_pattern     The closing pattern identifying the metadata
     * @param array  $notices             The notices generated during the dataset building
     * @param string $prefix              The complex DCAT entity being built
     *
     * @return string|null The extracted metadata, or null if the metadata was not
     *                     present
     */
    private function metadataExtractionByPattern(string &$description, string $property_to_extract,
                                                 string $starting_pattern, string $closing_pattern,
                                                 array &$notices, string $prefix): ?string
    {
        $starting_pattern_present = \strpos($description, $starting_pattern);

        if (false === $starting_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Starting pattern for %s is absent',
                $prefix, $this->property, \ucfirst($property_to_extract)
            );

            return null;
        }

        $ending_pattern_present = \strpos($description, $closing_pattern, $starting_pattern_present);

        if (false === $ending_pattern_present) {
            $notices[] = \sprintf(
                '%s %s: Closing pattern not present for %s is absent',
                $prefix, $this->property, \ucfirst($property_to_extract)
            );

            return null;
        }

        $extracted_metadata = \substr(
            $description,
            $starting_pattern_present + \strlen($starting_pattern),
            $ending_pattern_present - ($starting_pattern_present + \strlen($starting_pattern))
        );

        if (false === $extracted_metadata) {
            $notices[] = \sprintf(
                '%s %s: Failed to extract %s from harvested description',
                $prefix, $this->property, \ucfirst($property_to_extract)
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

        $extracted_metadata = \trim($extracted_metadata);

        $notices[] = \sprintf(
            '%s %s: Extracted %s \'%s\' from harvested description',
            $prefix, $this->property, \ucfirst($property_to_extract), $extracted_metadata
        );

        return $extracted_metadata;
    }

    private function extractSynchronization(string &$description, array &$notices, string $prefix): ?bool
    {
        $notices[] = \sprintf(
            '%s %s: Determining if dataset is eligible for synchronization',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'Dataset delen', '[Dataset delen:',
            ']', $notices, $prefix
        );

        if (null === $data) {
            return null;
        }

        return 'ja' === \trim(\strtolower($data));
    }

    private function extractTitle(string &$description, array &$notices, $prefix): ?string
    {
        $notices[] = \sprintf(
            '%s %s: Attempting title metadata extraction',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'title', '[Titel dataset:',
            ']', $notices, $prefix
        );

        return (null !== $data)
            ? \trim($data)
            : null;
    }

    private function extractDescription(string &$description, array &$notices, $prefix,
                                        string $title): ?string
    {
        $notices[] = \sprintf(
            '%s %s: Attempting description metadata extraction',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'title', '[Omschrijving template:',
            ']', $notices, $prefix
        );

        if ('standaard' === $data) {
            $template = \file_get_contents(
                \sprintf(
                    '%s/%s',
                    __DIR__, '../../var/templates/description_standaard.tpl'
                )
            );
            $layers   = '';

            foreach ($data['geoserver_layers'] as $layer) {
                $layers = \sprintf(
                    '%s%s%s',
                    $layers,
                    PHP_EOL,
                    \sprintf(
                        ' - %s',
                        \sprintf('https://services.nijmegen.nl/geoserver/%s/ows?', $layer)
                    )
                );
            }

            return \sprintf($template, $title, $layers);
        }

        return null;
    }

    private function extractThemes(string &$description, array &$notices, $prefix): array
    {
        $notices[] = \sprintf(
            '%s %s: Attempting theme metadata extraction',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'theme', '[Thema dataset:',
            ']', $notices, $prefix
        );

        $themes = \explode(',', $data);

        for ($i = 0; $i < \count($themes); ++$i) {
            $themes[$i] = \trim($themes[$i]);
        }

        $notices[] = \sprintf(
            '%s %s: Extracted %s theme(s) from harvested description',
            $prefix, $this->property, \count($themes)
        );

        return \array_values($themes);
    }

    private function extractHighValue(string &$description, array &$notices, string $prefix): bool
    {
        $notices[] = \sprintf(
            '%s %s: Determining if dataset is considered high value',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'highValue',
            '[Dataset onderdeel High Value dataset:', ']', $notices,
            $prefix
        );

        if (null === $data) {
            return false;
        }

        return 'ja' === \trim(\strtolower($data));
    }
}
