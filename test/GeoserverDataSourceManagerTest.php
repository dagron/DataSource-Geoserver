<?php

namespace NijmegenSync\Test\DataSource\Geoserver;

use NijmegenSync\DataSource\Geoserver\GeoserverDataSourceManager;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceManagerTest extends TestCase
{
    public function testIsNotInitializedByDefault(): void
    {
        $manager = new GeoserverDataSourceManager();

        $this->assertFalse($manager->isInitialized());
    }
}
