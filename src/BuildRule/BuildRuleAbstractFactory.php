<?php

namespace NijmegenSync\DataSource\Geoserver\BuildRule;

use NijmegenSync\Dataset\Builder\IDatasetBuildRule;

/**
 * Class BuildRuleAbstractFactory.
 *
 * Exposes all the custom build rules defined for the Nijmegen geoserver.
 */
class BuildRuleAbstractFactory
{
    /**
     * Returns all the defined custom build rules for the harvesting of the Nijmegen geoserver.
     *
     * @return IDatasetBuildRule[] The custom build rules to use
     */
    public static function getAll(): array
    {
        return [
            'description' => new DatasetDescriptionBuildRule(),
        ];
    }
}
