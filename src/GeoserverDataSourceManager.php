<?php

namespace NijmegenSync\DataSource\Geoserver;

use NijmegenSync\Contracts\BaseNijmegenSyncModule;
use NijmegenSync\Contracts\Exception\InitializationException;
use NijmegenSync\DataSource\Geoserver\Harvester\GeoserverDataSourceHarvester;
use NijmegenSync\DataSource\Harvesting\HarvestingFrequency;
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
    public function __construct()
    {
        parent::__construct();
    }

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
        return HarvestingFrequency::DAILY;
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
        if (null == $this->file_system_helper) {
            throw new InitializationException(
                'initialize() requires that a IFileSystemHelper implementation is assigned'
            );
        }

        $this->is_initialized = true;
    }
}
