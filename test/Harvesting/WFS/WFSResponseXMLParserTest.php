<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WFS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WFS\WFSResponseXMLParser;
use PHPUnit\Framework\TestCase;

class WFSResponseXMLParserTest extends TestCase
{
    public function testReturnsEmptyListWhenNoEntitiesAreFound(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root>
            </root>
        '));

        $this->assertSame([], $parser->getAllEntities());
    }

    public function testReturnsAllTheChildrenOfFeatureTypeList(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <FeatureTypeList>
                    <FeatureType></FeatureType>
                    <FeatureType></FeatureType>
                </FeatureTypeList>
            </root>
        '));

        $this->assertTrue(2 === \count($parser->getAllEntities()));
    }
}
