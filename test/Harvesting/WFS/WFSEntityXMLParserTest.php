<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WFS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WFS\WFSEntityXMLParser;
use PHPUnit\Framework\TestCase;

class WFSEntityXMLParserTest extends TestCase
{
    public function testReturnsEmptyStringWhenNoNameIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame('', $parser->findName());
    }

    public function testReturnsNameWhenNameIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root>
                <Name>test</Name>
            </root>
        '));

        $this->assertSame('test', $parser->findName());
    }

    public function testFindNameIgnoresNamespace(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root>
                <Name>namespace:test</Name>
            </root>
        '));

        $this->assertSame('test', $parser->findName());
    }

    public function testReturnsEmptyStringWhenNoTitleIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame('', $parser->findTitle());
    }

    public function testReturnsTitleWhenTitleIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root>
                <Title>test</Title>
            </root>
        '));

        $this->assertSame('test', $parser->findTitle());
    }

    public function testReturnsEmptyStringWhenNoAbstractIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('<root></root>'));

        $this->assertSame('', $parser->findAbstract());
    }

    public function testReturnsAbstractWhenAbstractIsFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root>
                <Abstract>test</Abstract>
            </root>
        '));

        $this->assertSame('test', $parser->findAbstract());
    }

    public function testReturnsEmptyArrayWhenNoKeywordsAreFound(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:Keywords />
            </root>
        '));

        $this->assertTrue(0 == \count($parser->findKeywords()));
    }

    public function testReturnsTheFoundKeywords(): void
    {
        $parser = new WFSEntityXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:Keywords>
                    <ows:Keyword>test1</ows:Keyword>
                    <ows:Keyword>test2</ows:Keyword>
                </ows:Keywords>
            </root>
        '));

        $this->assertTrue(2 == \count($parser->findKeywords()));
        $this->assertSame(
            ['test1', 'test2'],
            $parser->findKeywords()
        );
    }
}
