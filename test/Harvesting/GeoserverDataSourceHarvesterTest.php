<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvester;

use NijmegenSync\DataSource\Geoserver\Harvesting\GeoserverDataSourceHarvester;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceHarvesterTest extends TestCase
{
    public function testRequiresNoAuthenticationDetails(): void
    {
        $harvester = new GeoserverDataSourceHarvester();

        $this->assertFalse($harvester->requiresAuthenticationDetails());
    }
}
