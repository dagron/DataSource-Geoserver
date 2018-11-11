<?php

use NijmegenSync\Contracts\Exception\InitializationException;
use NijmegenSync\DataSource\Geoserver\GeoserverDataSourceManager;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceManagerTest extends TestCase
{
    public function testInitializationFailsWithoutFileSystemHelper(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->initialize();
            $this->fail();
        } catch (InitializationException $e) {
            $this->assertSame('initialize() requires that a IFileSystemHelper implementation is assigned', $e->getMessage());
        }
    }
}
