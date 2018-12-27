<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WMS\WMSLayerXMLParser;
use PHPUnit\Framework\TestCase;

class WMSLayerXMLParserTest extends TestCase
{
    public function testReturnsEmptyStringWhenNoNameIsFound(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <notAName>test</notAName>
            </root>
        '));

        $this->assertSame('', $parser->findName());
    }

    public function testReturnsNameWhenPresent(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <Name>test</Name>
            </root>
        '));

        $this->assertSame('test', $parser->findName());
    }

    public function testReturnsEmptyStringWhenNoTitleIsFound(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <notATitle>test</notATitle>
            </root>
        '));

        $this->assertSame('', $parser->findTitle());
    }

    public function testReturnsTitleWhenPresent(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <Title>test</Title>
            </root>
        '));

        $this->assertSame('test', $parser->findTitle());
    }

    public function testReturnsEmptyStringWhenNoAbstractIsFound(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <notAAbstract>test</notAAbstract>
            </root>
        '));

        $this->assertSame('', $parser->findAbstract());
    }

    public function testReturnsAbstractWhenPresent(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <Abstract>test</Abstract>
            </root>
        '));

        $this->assertSame('test', $parser->findAbstract());
    }

    public function testReturnsEmptyStringWhenNoBoundingBoxIsFound(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <notABoundingBox>test</notABoundingBox>
            </root>
        '));

        $this->assertSame('', $parser->findBoundingBox());
    }

    public function testReturnsBoundingBoxWhenPresent(): void
    {
        $parser = new WMSLayerXMLParser(new \SimpleXMLElement('
            <root>
                <BoundingBox SRS="EPSG:28992" minx="test1" miny="test2" maxx="test3" maxy="test4" />
            </root>
        '));

        $this->assertSame('test1,test2,test3,test4', $parser->findBoundingBox());
    }
}
