<?php

namespace NijmegenSync\Test\DataSource\Harvesting;

use NijmegenSync\DataSource\Geoserver\Harvesting\GeoserverXMLParser;
use PHPUnit\Framework\TestCase;

class GeoserverXMLParserTest extends TestCase
{
    public function testReturnsEmptyListWhenNoEntitiesAreFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame([], $parser->getAllEntities());
    }
}
