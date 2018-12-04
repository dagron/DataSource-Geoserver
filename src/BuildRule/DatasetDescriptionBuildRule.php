<?php

namespace NijmegenSync\DataSource\Geoserver\BuildRule;

use DCAT_AP_DONL\DCATDataset;
use DCAT_AP_DONL\DCATLiteral;
use NijmegenSync\Dataset\Builder\IDatasetBuildRule;

/**
 * Class DatasetDescriptionBuildRule.
 *
 * This custom build rule defines the description of a Nijmegen geoserver dataset based on a
 * pre-defined template.
 */
class DatasetDescriptionBuildRule implements IDatasetBuildRule
{
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
        if (isset($data['description']) && '' !== \trim($data['description'])) {
            $dataset->setDescription(new DCATLiteral($data['description']));
            $notices[] = \sprintf('%s Description: using description found in geoserver', $prefix);

            return;
        }

        $template = \file_get_contents(
            \sprintf('%s/%s', __DIR__, '../../var/description_template.tpl')
        );

        $layers = '';

        foreach ($data['geoserver_layers'] as $layer) {
            $layers = \sprintf(
                '%s%s%s',
                $layers,
                PHP_EOL,
                \sprintf(
                    ' - %s',
                    \sprintf('https://services.nijmegen.nl/geoserver/%s/wfs?', $layer)
                )
            );
        }

        $dataset->setDescription(new DCATLiteral(\sprintf($template, $data['title'], $layers)));
        $notices[] = \sprintf('%s Description: No description found, using description template', $prefix);
    }
}
