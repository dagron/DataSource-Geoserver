<?php

namespace NijmegenSync\DataSource\Geoserver;

use NijmegenSync\Contracts\BaseNijmegenSyncModule;
use NijmegenSync\DataSource\Geoserver\BuildRule\BuildRuleAbstractFactory;
use NijmegenSync\DataSource\Geoserver\Harvesting\GeoserverDataSourceHarvester;
use NijmegenSync\DataSource\Harvesting\HarvestingFrequency;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use NijmegenSync\DataSource\IDataSourceManager;
use NijmegenSync\Exception\InitializationException;
use NijmegenSync\Exception\IOException;

/**
 * Class GeoserverDataSourceManager.
 */
class GeoserverDataSourceManager extends BaseNijmegenSyncModule implements IDataSourceManager
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $harvesting_frequency;

    /** @var string */
    protected $defaults_file_path;

    /** @var string */
    protected $value_mappings_file_path;

    /** @var string */
    protected $blacklist_mappings_file_path;

    /** @var string */
    protected $whitelist_mappings_file_path;

    /** @var string */
    protected $base_uri;

    /** @var string[] */
    protected $layers;

    /** @var GeoserverDataSourceHarvester */
    protected $harvester;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct();

        $this->base_uri  = null;
        $this->harvester = null;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(): void
    {
        if ($this->isInitialized()) {
            throw new InitializationException('module is already initialized');
        }

        if (null == $this->file_system_helper) {
            throw new InitializationException('module requires IFileSystemHelper for initialization');
        }

        try {
            $settings_file     = \sprintf('%s/%s', __DIR__, '../var/settings.json');
            $settings_contents = $this->file_system_helper->readFile($settings_file);
            $settings_json     = \json_decode($settings_contents, true);
            $settings_keys     = ['name', 'harvesting_frequency', 'base_uri', 'layers'];

            foreach ($settings_keys as $key) {
                if (!\array_key_exists($key, $settings_json)) {
                    throw new InitializationException(
                        \sprintf('settings file is missing key %s', $key)
                    );
                }

                $this->$key = $settings_json[$key];
            }

            $settings_file_keys = [
                'defaults_file_path', 'value_mappings_file_path', 'blacklist_mappings_file_path',
                'whitelist_mappings_file_path',
            ];

            foreach ($settings_file_keys as $key) {
                if (!\array_key_exists($key, $settings_json)) {
                    throw new InitializationException(
                        \sprintf('settings file is missing key %s', $key)
                    );
                }

                $this->$key = \sprintf('%s/%s/%s', __DIR__, '../var', $settings_json[$key]);
            }

            if (!HarvestingFrequency::isValid($this->harvesting_frequency)) {
                throw new InitializationException(
                    \sprintf('module declared illegal harvesting frequency %s', $this->harvesting_frequency)
                );
            }

            $this->harvester = new GeoserverDataSourceHarvester();
            $this->harvester->setBaseURI($this->base_uri);

            $this->is_initialized = true;
        } catch (IOException $e) {
            throw new InitializationException($e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvestingFrequency(): string
    {
        return $this->harvesting_frequency;
    }

    /**
     * {@inheritdoc}
     */
    public function getHarvester(): IDataSourceHarvester
    {
        return $this->harvester;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultsFilePath(): string
    {
        return $this->defaults_file_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueMappingFilePath(): string
    {
        return $this->value_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlacklistMappingFilePath(): string
    {
        return $this->blacklist_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getWhitelistMappingFilePath(): string
    {
        return $this->whitelist_mappings_file_path;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomBuildRules(): array
    {
        return BuildRuleAbstractFactory::getAll();
    }
}
