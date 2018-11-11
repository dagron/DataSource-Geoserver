<?php

namespace NijmegenSync\DataSource\Geoserver;

use NijmegenSync\Contracts\BaseNijmegenSyncModule;
use NijmegenSync\Contracts\Exception\InitializationException;
use NijmegenSync\DataSource\Geoserver\Harvester\GeoserverDataSourceHarvester;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use NijmegenSync\DataSource\IDataSourceManager;

/**
 * Class GeoserverDataSourceManager.
 */
class GeoserverDataSourceManager extends BaseNijmegenSyncModule implements IDataSourceManager
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvestingFrequency(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvester(): IDataSourceHarvester
    {
        return new GeoserverDataSourceHarvester();
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultsFilePath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getValueMappingFilePath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlacklistMappingFilePath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistMappingFilePath(): string
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        throw new InitializationException('not implemented');
    }
}
