<?php

namespace NijmegenSync\DataSource\Geoserver\BuildRule;

use NijmegenSync\Dataset\Builder\IDatasetBuildRule;
use NijmegenSync\Dataset\Builder\IDistributionBuildRule;

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
    public static function getAllDatasetBuildRules(): array
    {
        return [
            '_before' => new PreparingBuildRule('_before'),
        ];
    }

    /**
     * Returns all the defined custom build rules for the distribution build steps to replace.
     *
     * @return IDistributionBuildRule[] The custom build rules to use
     */
    public static function getAllDistributionBuildRules(): array
    {
        return [];
    }
}
