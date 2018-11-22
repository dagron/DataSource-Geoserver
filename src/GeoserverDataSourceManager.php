<?php

namespace NijmegenSync\DataSource\Geoserver;

use NijmegenSync\Contracts\BaseNijmegenSyncModule;
use NijmegenSync\DataSource\Geoserver\Harvester\GeoserverDataSourceHarvester;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use NijmegenSync\DataSource\IDataSourceManager;

/**
 * Class GeoserverDataSourceManager.
 */
class GeoserverDataSourceManager extends BaseNijmegenSyncModule implements IDataSourceManager
{
    /** @var string */
    protected $base_uri;

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        // TODO: Implement initialize() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        // TODO: Implement getName() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvestingFrequency(): string
    {
        // TODO: Implement getHarvestingFrequency() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvester(): IDataSourceHarvester
    {
        $harvester = new GeoserverDataSourceHarvester();
        $harvester->setBaseURI($this->base_uri);

        return $harvester;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultsFilePath(): string
    {
        // TODO: Implement getDefaultsFilePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getValueMappingFilePath(): string
    {
        // TODO: Implement getValueMappingFilePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getBlacklistMappingFilePath(): string
    {
        // TODO: Implement getBlacklistMappingFilePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistMappingFilePath(): string
    {
        // TODO: Implement getWhitelistMappingFilePath() method.
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomBuildRules(): array
    {
        // TODO: Implement getCustomBuildRules() method.
    }
}
