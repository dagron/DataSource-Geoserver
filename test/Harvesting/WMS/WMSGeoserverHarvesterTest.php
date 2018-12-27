<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WMS\WMSGeoserverHarvester;
use PHPUnit\Framework\TestCase;

class WMSGeoserverHarvesterTest extends TestCase
{
    public function testBaseURLGettingAndSetting(): void
    {
        $harvester = new WMSGeoserverHarvester();
        $harvester->setBaseUrl('https://my.test.url');

        $this->assertSame(
            'https://my.test.url',
            $harvester->getBaseUrl()
        );
    }

    public function testResourceResolutionsGettingAndSetting(): void
    {
        $harvester = new WMSGeoserverHarvester();
        $harvester->setResourceResolutions([
            ['a' => 'b'],
            ['c' => 'd'],
        ]);

        $this->assertSame(
            [
                ['a' => 'b'],
                ['c' => 'd'],
            ],
            $harvester->getResourceResolutions()
        );
    }
}
