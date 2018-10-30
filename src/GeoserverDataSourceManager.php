<?php

namespace GeoserverDataSource;

use Doctrine\ORM\EntityManager;
use GeoserverDataSource\Harvester\GeoserverDataSourceHarvester;
use NijmegenSync\Contracts\Exceptions\InitializationException;
use NijmegenSync\Contracts\Exceptions\IOException;
use NijmegenSync\Contracts\IFileSystemHelper;
use NijmegenSync\DataSource\Harvesting\HarvestingFrequency;
use NijmegenSync\DataSource\Harvesting\IDataSourceHarvester;
use NijmegenSync\DataSource\IDataSourceManager;


/**
 * Class GeoserverDataSourceManager
 *
 * @package GeoserverDataSource
 */
class GeoserverDataSourceManager implements IDataSourceManager {

    /** @var IFileSystemHelper */
    protected $file_system_helper;

    /** @var EntityManager */
    protected $entity_manager;

    /** @var string */
    protected $name;

    /** @var string */
    protected $harvesting_frequency;

    /** @var array */
    protected $api_paths;

    /** @var string */
    protected $file_defaults;

    /** @var string */
    protected $file_value_mapping;

    /** @var string */
    protected $file_blacklist_mapping;

    /** @var string */
    protected $file_whitelist_mapping;

    /**
     * Getter method for the name of the DataSource.
     *
     * @return string The name of the DataSource
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Getter method for the harvesting frequency of this DataSource.
     *
     * @return string The harvesting frequency of this DataSource
     */
    public function getHarvestingFrequency(): string
    {
        return $this->harvesting_frequency;
    }

    /**
     * Getter method for the actual DataSourceHarvester implementation which will harvest the
     * DataSource.
     *
     * @return IDataSourceHarvester The implementation which will harvest the DataSource
     */
    public function getHarvester(): IDataSourceHarvester
    {
        $harvester = new GeoserverDataSourceHarvester();
        $harvester->setDataOverviewURL($this->api_paths['overview']);
        $harvester->setDataRetrievalURL($this->api_paths['retrieval']);

        return $harvester;
    }

    /**
     * Getter method for the absolute path to the ValueMapping file.
     *
     * @return string The absolute path to the ValueMapping file
     */
    public function getValueMappingFilePath(): string
    {
        return $this->file_value_mapping;
    }

    /**
     * Getter method for the absolute path to the BlacklistMapping file.
     *
     * @return string The absolute path to the BlacklistMapping file
     */
    public function getBlacklistMappingFilePath(): string
    {
        return $this->file_blacklist_mapping;
    }

    /**
     * Getter method for the absolute path to the WhitelistMapping file.
     *
     * @return string The absolute path to the WhitelistMapping file
     */
    public function getWhitelistMappingFilePath(): string
    {
        return $this->file_whitelist_mapping;
    }

    /**
     * INijmegenSyncModule constructor.
     *
     * Ensures that no implementation has a complex constructor assuring that the NijmegenSync
     * core application can correctly instantiate the implementation of this interface.
     */
    public function __construct()
    {
    }

    /**
     * Initializes the module.
     *
     * @throws InitializationException Thrown if the module fails to initialize
     */
    public function initialize(): void
    {
        if (!$this->file_system_helper) {
            throw new InitializationException(
                'GeoserverDataSourceManager requires a FileSystemHelper before the module can be initialized'
            );
        }

        try {
            $settings_file = sprintf('%s/../settings.json', __DIR__);
            $json_file_contents = json_decode($this->file_system_helper->readFile($settings_file), true);
            $keys = ['name', 'harvesting_frequency', 'api_paths', 'file_defaults', 'file_value_mapping', 'file_blacklist_mapping', 'file_whitelist_mapping'];
            foreach ($keys as $key) {
                if (!array_key_exists($key, $json_file_contents)) {
                    throw new InitializationException(
                        sprintf('settings file is missing key %s', $key)
                    );
                }
                $this->$key = $json_file_contents[$key];
            }

            if (!HarvestingFrequency::isValid($this->harvesting_frequency)) {
                throw new InitializationException('settings file has defined a illegal harvesting frequency');
            }

            if ($this->file_system_helper->fileExists($this->file_defaults)) {
                throw new InitializationException('defined defaults file does not exist');
            }

            if ($this->file_system_helper->fileExists($this->file_value_mapping)) {
                throw new InitializationException('defined value mapping file does not exist');
            }

            if ($this->file_system_helper->fileExists($this->file_blacklist_mapping)) {
                throw new InitializationException('defined blacklist mapping file does not exist');
            }

            if ($this->file_system_helper->fileExists($this->file_whitelist_mapping)) {
                throw new InitializationException('defined whitelist mapping file does not exist');
            }
        } catch (IOException $e) {
            throw new InitializationException($e);
        }
    }

    /**
     * Assigns a FileSystemHelper to the NijmegenSync module.
     *
     * @param IFileSystemHelper $helper The FileSystemHelper to use
     */
    public function setFileSystemHelper(IFileSystemHelper $helper): void
    {
        $this->file_system_helper = $helper;
    }

    /**
     * Assigns a database EntityManager to the NijmegenSync module.
     *
     * @param EntityManager $manager The EntityManager to use
     */
    public function setEntityManager(EntityManager $manager): void
    {
        $this->entity_manager = $manager;
    }

}
