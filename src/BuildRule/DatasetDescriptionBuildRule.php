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
    /**
     * {@inheritdoc}
     */
    public function applyRule(DCATDataset $dataset, array $data, array $defaults,
                              array $value_mappers, array $blacklist_mappers,
                              array $whitelist_mappers): void
    {
        $template = \file_get_contents(
            \sprintf('%s/%s', __DIR__, '../../var/description_template.tpl')
        );

        $dataset->setDescription(new DCATLiteral(\sprintf($template, $data['title'])));
    }
}