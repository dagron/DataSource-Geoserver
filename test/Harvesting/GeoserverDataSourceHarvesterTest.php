<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvester;

use NijmegenSync\Contracts\IAuthenticationDetails;
use NijmegenSync\DataSource\Geoserver\Harvesting\GeoserverDataSourceHarvester;
use NijmegenSync\Exception\AuthenticationDetailException;
use PHPUnit\Framework\TestCase;

class GeoserverDataSourceHarvesterTest extends TestCase
{
    public function testRequiresNoAuthenticationDetails(): void
    {
        $harvester = new GeoserverDataSourceHarvester();

        $this->assertFalse($harvester->requiresAuthenticationDetails());
    }

    public function testNoExceptionIsThrownWhenProvidingAuthenticationDetails(): void
    {
        try {
            $harvester = new GeoserverDataSourceHarvester();
            $harvester->setAuthenticationDetails(new class() implements IAuthenticationDetails {
                public function getDetailsAsKeyValueArray(): array
                {
                    return [];
                }

                public function setProperty(string $property, string $value): void
                {
                }

                public function getProperty(string $property): string
                {
                    return '';
                }
            });

            $this->assertTrue(true);
        } catch (AuthenticationDetailException $e) {
            $this->fail('unexpected AuthenticationDetailException thrown');
        }
    }

    public function testBaseURISettingAndGetting(): void
    {
        $harvester = new GeoserverDataSourceHarvester();
        $harvester->setBaseURI(['test']);

        $this->assertSame(['test'], $harvester->getBaseUri());
    }
}
