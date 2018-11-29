<?php

namespace NijmegenSync\Test\DataSource\Harvesting;

use NijmegenSync\DataSource\Geoserver\Harvesting\GeoserverXMLParser;
use PHPUnit\Framework\TestCase;

class GeoserverXMLParserTest extends TestCase
{
    public function testReturnsEmptyListWhenNoEntitiesAreFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('
            <root>
            </root>
        '));

        $this->assertSame([], $parser->getAllEntities());
    }

    public function testReturnsAllTheChildrenOfFeatureTypeList(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('
            <root>
                <FeatureTypeList>
                    <a></a>
                    <b></b>
                </FeatureTypeList>
            </root>
        '));

        $this->assertTrue(\count($parser->getAllEntities()) === 2);
    }

    public function testReturnsEmptyStringWhenNoTitleIsFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame('', $parser->findTitle());
    }

    public function testReturnsTitleWhenTitleIsFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('
            <root>
                <Title>test</Title>
            </root>
        '));

        $this->assertSame('test', $parser->findTitle());
    }

    public function testReturnsEmptyStringWhenNoAbstractIsFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame('', $parser->findAbstract());
    }

    public function testReturnsAbstractWhenAbstractIsFound(): void
    {
        $parser = new GeoserverXMLParser(new \SimpleXMLElement('
            <root>
                <Abstract>test</Abstract>
            </root>
        '));

        $this->assertSame('test', $parser->findAbstract());
    }
}
