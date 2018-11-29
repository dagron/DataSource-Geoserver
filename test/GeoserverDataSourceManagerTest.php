<?php

namespace NijmegenSync\Test\DataSource\Geoserver;

use NijmegenSync\Contracts\IFileSystemHelper;
use NijmegenSync\DataSource\Geoserver\GeoserverDataSourceManager;
use NijmegenSync\Exception\InitializationException;
use NijmegenSync\Exception\IOException;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceManagerTest extends TestCase
{
    public function testIsNotInitializedByDefault(): void
    {
        $manager = new GeoserverDataSourceManager();

        $this->assertFalse($manager->isInitialized());
    }

    public function testInitializeThrowsExceptionWithoutAFileSystemHelper(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->initialize();

            $this->fail('initialize failed to throw exception');
        } catch (InitializationException $e) {
            $this->assertEquals(
                'module requires IFileSystemHelper for initialization',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingNameWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getName();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve name, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingHarvestingFrequencyWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getHarvestingFrequency();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve harvesting_frequency, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingHarvesterWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getHarvester();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve harvester, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingDefaultsWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getDefaultsFilePath();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve defaults_file_path, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingValueMappingsWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getValueMappingFilePath();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve value_mappings_file_path, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingBlacklistMappingsWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getBlacklistMappingFilePath();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve blacklist_mappings_file_path, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsExceptionWhenGettingWhitelistMappingsWithoutInitialize(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->getWhitelistMappingFilePath();

            $this->fail('failed to throw exception when module is not initialized');
        } catch (InitializationException $e) {
            $this->assertSame(
                'cannot retrieve whitelist_mappings_file_path, module has not been initialized',
                $e->getMessage()
            );
        }
    }

    public function testThrowsInitializationExceptionWhenTheSettingsFileIsNotFound(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockNotFoundFileSystemHelper());

        try {
            $manager->initialize();

            $this->fail('initialize failed to throw exception');
        } catch (InitializationException $e) {
            $this->assertTrue(true);
        }
    }

    public function testThrowsInitializationExceptionOnMissingKeys(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockFileSystemHelperWithoutName());

        try {
            $manager->initialize();
        } catch (InitializationException $e) {
            $this->assertEquals(
                'settings file is missing key name',
                $e->getMessage()
            );
        }
    }

    public function testThrowsInitializationExceptionOnMissingFileKeys(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockFileSystemHelperWithoutValueMapper());

        try {
            $manager->initialize();
        } catch (InitializationException $e) {
            $this->assertEquals(
                'settings file is missing key value_mappings_file_path',
                $e->getMessage()
            );
        }
    }

    public function testThrowsInitializationExceptionOnIllegalHarvestingFrequency(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockFileSystemHelperWithInvalidFrequency());

        try {
            $manager->initialize();
        } catch (InitializationException $e) {
            $this->assertEquals(
                'module declared illegal harvesting frequency yearly',
                $e->getMessage()
            );
        }
    }

    public function testThrowsInitializationExceptionWhenModuleIsAlreadyInitialized(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockFileSystemHelper());

        try {
            $manager->initialize();

            $this->assertTrue($manager->isInitialized());

            $manager->initialize();
        } catch (InitializationException $e) {
            $this->assertEquals(
                'module is already initialized',
                $e->getMessage()
            );
        }
    }

    public function testGettersFunctionWhenInitialized(): void
    {
        $manager = new GeoserverDataSourceManager();
        $manager->setFileSystemHelper($this->mockFileSystemHelper());

        try {
            $manager->initialize();

            $this->assertTrue($manager->isInitialized());
            $this->assertNotNull($manager->getName());
            $this->assertNotNull($manager->getHarvestingFrequency());
            $this->assertNotNull($manager->getHarvester());
            $this->assertNotNull($manager->getDefaultsFilePath());
            $this->assertNotNull($manager->getValueMappingFilePath());
            $this->assertNotNull($manager->getBlacklistMappingFilePath());
            $this->assertNotNull($manager->getWhitelistMappingFilePath());
        } catch (InitializationException $e) {
            $this->fail('unexpected Exception thrown');
        }
    }

    public function testReturnsAllTheBuildRules(): void
    {
        $manager = new GeoserverDataSourceManager();
        $rules   = $manager->getCustomBuildRules();

        $this->assertTrue(2 == \count($rules));
        $this->assertArrayHasKey('_before', $rules);
        $this->assertArrayHasKey('description', $rules);
    }

    private function mockNotFoundFileSystemHelper(): IFileSystemHelper
    {
        return new class() implements IFileSystemHelper {
            public function readFile(string $file): string
            {
                throw new IOException();
            }

            public function exists(string $path): bool
            {
                return false;
            }

            public function readDirectory(string $directory, string $pattern = '*', int $limit = 0): array
            {
                return [];
            }

            public function write(string $path, string $contents): void
            {
            }
        };
    }

    private function mockFileSystemHelperWithoutName(): IFileSystemHelper
    {
        return new class() implements IFileSystemHelper {
            public function readFile(string $file): string
            {
                return '
                    {
                        "harvesting_frequency":         "daily",
                        "base_uri":                     "https://services.nijmegen.nl",
                        "layers_uri":                   "https://nijmegen-acc.textinfo.nl/nijmegensync/geoserver-workspaces",
                        "defaults_file_path":           "defaults.json",
                        "blacklist_mappings_file_path": "blacklist_mappings.json",
                        "whitelist_mappings_file_path": "whitelist_mappings.json"
                    }
                ';
            }

            public function exists(string $path): bool
            {
                return false;
            }

            public function readDirectory(string $directory, string $pattern = '*', int $limit = 0): array
            {
                return [];
            }

            public function write(string $path, string $contents): void
            {
            }
        };
    }

    private function mockFileSystemHelperWithoutValueMapper(): IFileSystemHelper
    {
        return new class() implements IFileSystemHelper {
            public function readFile(string $file): string
            {
                return '
                    {
                        "name":                         "geoserver",
                        "harvesting_frequency":         "daily",
                        "base_uri":                     "https://services.nijmegen.nl",
                        "layers_uri":                   "https://nijmegen-acc.textinfo.nl/nijmegensync/geoserver-workspaces",
                        "defaults_file_path":           "defaults.json",
                        "blacklist_mappings_file_path": "blacklist_mappings.json",
                        "whitelist_mappings_file_path": "whitelist_mappings.json"
                    }
                ';
            }

            public function exists(string $path): bool
            {
                return false;
            }

            public function readDirectory(string $directory, string $pattern = '*', int $limit = 0): array
            {
                return [];
            }

            public function write(string $path, string $contents): void
            {
            }
        };
    }

    private function mockFileSystemHelperWithInvalidFrequency(): IFileSystemHelper
    {
        return new class() implements IFileSystemHelper {
            public function readFile(string $file): string
            {
                return '
                    {
                        "name":                         "geoserver",
                        "harvesting_frequency":         "yearly",
                        "base_uri":                     "https://services.nijmegen.nl",
                        "layers_uri":                   "https://nijmegen-acc.textinfo.nl/nijmegensync/geoserver-workspaces",
                        "defaults_file_path":           "defaults.json",
                        "value_mappings_file_path":     "value_mappings.json",
                        "blacklist_mappings_file_path": "blacklist_mappings.json",
                        "whitelist_mappings_file_path": "whitelist_mappings.json"
                    }
                ';
            }

            public function exists(string $path): bool
            {
                return false;
            }

            public function readDirectory(string $directory, string $pattern = '*', int $limit = 0): array
            {
                return [];
            }

            public function write(string $path, string $contents): void
            {
            }
        };
    }

    private function mockFileSystemHelper(): IFileSystemHelper
    {
        return new class() implements IFileSystemHelper {
            public function readFile(string $file): string
            {
                return '
                    {
                        "name":                         "geoserver",
                        "harvesting_frequency":         "daily",
                        "base_uri":                     "https://services.nijmegen.nl",
                        "layers_uri":                   "https://nijmegen-acc.textinfo.nl/nijmegensync/geoserver-workspaces",
                        "defaults_file_path":           "defaults.json",
                        "value_mappings_file_path":     "value_mappings.json",
                        "blacklist_mappings_file_path": "blacklist_mappings.json",
                        "whitelist_mappings_file_path": "whitelist_mappings.json"
                    }
                ';
            }

            public function exists(string $path): bool
            {
                return false;
            }

            public function readDirectory(string $directory, string $pattern = '*', int $limit = 0): array
            {
                return [];
            }

            public function write(string $path, string $contents): void
            {
            }
        };
    }
}
