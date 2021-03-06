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
     * Attempts to extract metadata from the harvested description so that a proper dataset can be
     * built from the harvested data of the geoserver.
     *
     * Will indicate that the dataset building process should be aborted if no description was
     * harvested or if the harvested description indicates that the dataset should not be considered
     * for synchronization.
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
                'Metadata indicates the dataset should not be harvested as no description was harvested'
            );
        }

        if (null == $data['description'] || '' == \trim($data['description'])) {
            throw new AbortDatasetBuilderException(
                'Metadata indicates the dataset should not be harvested as no description is present'
            );
        }

        $should_synchronize = $this->extractSynchronization(
            $data['description'], $notices, $prefix
        );

        if (null === $should_synchronize) {
            throw new AbortDatasetBuilderException(
                'Metadata indicates dataset should not be harvested as the \'Dataset delen\' vocabulary is absent'
            );
        }

        if (!$should_synchronize) {
            throw new AbortDatasetBuilderException(
                'Metadata indicates dataset should not be harvested as \'Dataset delen\' is set to \'nee\''
            );
        }

        $data['title']       = $this->extractTitle(
            $data['description'], $notices, $prefix
        );
        $data['theme']       = $this->extractThemes(
            $data['description'], $notices, $prefix
        );
        $data['highValue']   = $this->extractHighValue(
            $data['description'], $notices, $prefix
        );
        $data['description'] = $this->extractDescription(
            $data['description'], $notices, $prefix, $data['title'], $data
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
     * @return null|string The extracted metadata, or null if the metadata was not
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

    /**
     * Attempts to extract metadata from the given description which indicates whether or not this
     * dataset should be synchronized to a target application.
     *
     * The pattern that is searched for is [Dataset delen: {ja/nee}]. Where 'ja' equates to true and
     * 'nee' equates to false.
     *
     * When the pattern is found it will be cut from the description.
     *
     * @param string   $description The description to extract data from
     * @param string[] $notices     The notices generated during the dataset building process
     * @param string   $prefix      The DCAT ComplexEntity being built
     *
     * @return null|bool True or false if the metadata is present, null otherwise
     */
    private function extractSynchronization(string &$description, array &$notices, string $prefix): ?bool
    {
        $notices[] = \sprintf(
            '%s %s: Determining if dataset is eligible for synchronization',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'Dataset delen', '[Delen dataset:',
            ']', $notices, $prefix
        );

        if (null === $data) {
            return null;
        }

        return 'ja' === \trim(\strtolower($data));
    }

    /**
     * Attempts to extract metadata from the given description which contains the title of the
     * dataset as it should be on a target application.
     *
     * The pattern that is searched for is [Title dataset: {title}].
     *
     * When the pattern is found it will be cut from the description.
     *
     * @param string   $description The description to extract data from
     * @param string[] $notices     The notices generated during the dataset building process
     * @param string   $prefix      The DCAT ComplexEntity being built
     *
     * @return null|string The extracted title or null if the pattern was absent
     */
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

    /**
     * Attempts to extract metadata from the given description which contains the description of the
     * dataset as it should be on a target application.
     *
     * The pattern that is searched for is [Omschrijving template: {template_name}].
     *
     * Supported template names:
     * - standaard
     *
     * When the pattern is found it will be cut from the description.
     *
     * @param string   $description The description to extract data from
     * @param string[] $notices     The notices generated during the dataset building process
     * @param string   $prefix      The DCAT ComplexEntity being built
     * @param string   $title       The title of the dataset being built
     * @param array    $data        The harvested data
     *
     * @return null|string The generated description template or null if the pattern was
     *                     absent
     */
    private function extractDescription(string &$description, array &$notices, string $prefix,
                                        string $title, array $data): ?string
    {
        $notices[] = \sprintf(
            '%s %s: Attempting description template metadata extraction',
            $prefix, $this->property
        );

        $template_input = $this->metadataExtractionByPattern(
            $description, 'template', '[Omschrijving template:',
            ']', $notices, $prefix
        );

        switch ($template_input) {
            case 'WMS':
                $template = \file_get_contents(
                    \sprintf(
                        '%s/%s',
                        __DIR__, '../../var/templates/description_WMS.tpl'
                    )
                );
                $wms_url = \sprintf(
                    'https://services.nijmegen.nl/geoservices/wms/extern?&request=getCapabilities&service=WMS'
                );
                $generated_description = \sprintf($template, $title, $wms_url);

                break;
            case 'WMS-Foto':
                $template = \file_get_contents(
                    \sprintf(
                        '%s/%s',
                        __DIR__, '../../var/templates/description_WMS-Foto.tpl'
                    )
                );
                $wms_url = \sprintf(
                    'https://services.nijmegen.nl/geoservices/wms/extern?&request=getCapabilities&service=WMS'
                );
                $generated_description = \sprintf($template, $title, $wms_url);

                break;
            case 'WMS-Kaart':
                $template = \file_get_contents(
                    \sprintf(
                        '%s/%s',
                        __DIR__, '../../var/templates/description_WMS-Kaart.tpl'
                    )
                );
                $wms_url = \sprintf(
                    'https://services.nijmegen.nl/geoservices/wms/extern?&request=getCapabilities&service=WMS'
                );
                $generated_description = \sprintf($template, $title, $wms_url);

                break;
            case 'WFS':
                $template = \file_get_contents(
                    \sprintf(
                        '%s/%s',
                        __DIR__, '../../var/templates/description_WFS.tpl'
                    )
                );
                $wfs_url = \sprintf(
                    'https://services.nijmegen.nl/geoservices/%s/ows?request=getCapabilities&service=WFS', $data['geoserver_layer']
                );
                $wms_url = \sprintf(
                    'https://services.nijmegen.nl/geoservices/%s/ows?request=getCapabilities&service=WMS', $data['geoserver_layer']
                );

                $generated_description = \sprintf($template, $title, $wfs_url, $wms_url);

                break;
            case 'Standaard':
            default:
                $template = \file_get_contents(
                    \sprintf(
                        '%s/%s',
                        __DIR__, '../../var/templates/description_Standaard.tpl'
                    )
                );
                $generated_description = \sprintf($template, $title);

                break;
        }

        $notices[] = \sprintf(
            '%s %s: Using Description template %s',
            $prefix, $this->property, $template_input
        );

        return $generated_description;
    }

    /**
     * Attempts to extract metadata from the given description which contains the themes of the
     * dataset. Multiple themes may be extracted.
     *
     * The pattern that is searched for is [Thema dataset: {themes separated by a comma}].
     *
     * Supported themes:
     * - Bestuur
     * - Cultuur en recreatie
     * - Economie
     * - Financien
     * - Huisvesting
     * - Internationaal
     * - Landbouw
     * - Migratie en integratie
     * - Natuur en Milieu
     * - Onderwijs en wetenschap
     * - Openbare orde en veiligheid
     * - Recht
     * - Ruimte en infrastructuur
     * - Sociale zekerheid
     * - Verkeer
     * - Werk
     * - Zorg en gezondheid
     *
     * When the pattern is found it will be cut from the description.
     *
     * @param string   $description The description to extract data from
     * @param string[] $notices     The notices generated during the dataset building process
     * @param string   $prefix      The DCAT ComplexEntity being built
     *
     * @return string[] The extracted themes, may be empty
     */
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

        if (null == $data) {
            $notices[] = \sprintf(
                '%s %s: Extracted 0 themes from harvested description',
                $prefix, $this->property
            );

            return [];
        }

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

    /**
     * Attempts to extract metadata from the given description which contains whether or not the
     * dataset should be considered high value. Will default to false should the metadata be absent.
     *
     * The pattern that is searched for is [Dataset onderdeel High Value dataset: {ja/nee}].
     *
     * When the pattern is found it will be cut from the description.
     *
     * @param string   $description The description to extract data from
     * @param string[] $notices     The notices generated during the dataset building process
     * @param string   $prefix      The DCAT ComplexEntity being built
     *
     * @return string Whether or not the dataset should be considered high value
     */
    private function extractHighValue(string &$description, array &$notices, string $prefix): string
    {
        $notices[] = \sprintf(
            '%s %s: Determining if dataset is considered high value',
            $prefix, $this->property
        );

        $data = $this->metadataExtractionByPattern(
            $description, 'highValue',
            '[Dataset onderdeel High Value lijst:', ']', $notices,
            $prefix
        );

        if (null === $data) {
            return false;
        }

        return 'ja' === \trim(\strtolower($data)) ? 'true' : 'false';
    }
}
