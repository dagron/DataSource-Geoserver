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
}
