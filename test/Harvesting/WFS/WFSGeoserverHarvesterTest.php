<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WFS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WFS\WFSGeoserverHarvester;
use PHPUnit\Framework\TestCase;

class WFSGeoserverHarvesterTest extends TestCase
{
    public function testBaseURLGettingAndSetting(): void
    {
        $harvester = new WFSGeoserverHarvester();
        $harvester->setBaseUrl('https://my.test.url');

        $this->assertSame(
            'https://my.test.url',
            $harvester->getBaseUrl()
        );
    }

    public function testLayersURIGettingAndSetting(): void
    {
        $harvester = new WFSGeoserverHarvester();
        $harvester->setLayersUri('https://my.test.url');

        $this->assertSame(
            'https://my.test.url',
            $harvester->getLayersUri()
        );
    }
}
