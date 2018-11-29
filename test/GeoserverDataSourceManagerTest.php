<?php

namespace NijmegenSync\Test\DataSource\Geoserver;

use NijmegenSync\DataSource\Geoserver\GeoserverDataSourceManager;
use NijmegenSync\Exception\InitializationException;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceManagerTest extends TestCase
{
    public function testIsNotInitializedByDefault(): void
    {
        $manager = new GeoserverDataSourceManager();

        $this->assertFalse($manager->isInitialized());
    }

    public function testInitializeThrowsExceptionWhithoutAFileSystemHelper(): void
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

    public function testReturnsAllTheBuildRules(): void
    {
        $manager = new GeoserverDataSourceManager();
        $rules   = $manager->getCustomBuildRules();

        $this->assertTrue(2 == \count($rules));
        $this->assertArrayHasKey('_before', $rules);
        $this->assertArrayHasKey('description', $rules);
    }
}
