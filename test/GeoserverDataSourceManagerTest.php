<?php

use NijmegenSync\Contracts\Exception\InitializationException;
use NijmegenSync\DataSource\Geoserver\GeoserverDataSourceManager;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceManagerTest extends TestCase
{
    public function testInitializationFails(): void
    {
        $manager = new GeoserverDataSourceManager();

        try {
            $manager->initialize();
            $this->fail();
        } catch (InitializationException $e) {
            $this->assertSame('not implemented', $e->getMessage());
        }
    }
}
