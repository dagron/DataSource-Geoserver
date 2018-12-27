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

    public function testReturnsAnEmptyStringWhenNoContactNameCanBeFound(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider>
                    <ows:ServiceContact />
                </ows:ServiceProvider>
            </root>
        '));

        $this->assertSame('', $parser->findContactName());
    }

    public function testCanFindADefinedContactName(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider>
                    <ows:ServiceContact>
                        <ows:IndividualName>TestName</ows:IndividualName>
                    </ows:ServiceContact>
                </ows:ServiceProvider>
            </root>
        '));

        $this->assertSame('TestName', $parser->findContactName());
    }

    public function testReturnsAnEmptyStringWhenNoContactOrganizationCanBeFound(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider />
            </root>
        '));

        $this->assertSame('', $parser->findContactOrganization());
    }

    public function testCanFindADefinedContactOrganization(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider>
                    <ows:ProviderName>TestName</ows:ProviderName>
                </ows:ServiceProvider>
            </root>
        '));

        $this->assertSame('TestName', $parser->findContactOrganization());
    }

    public function testReturnsAnEmptyStringWhenNoContactEmailCanBeFound(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider />
            </root>
        '));

        $this->assertSame('', $parser->findContactEmail());
    }

    public function testCanFindADefinedContactEmail(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceProvider>
                    <ows:ServiceContact>
                        <ows:ContactInfo>
                            <ows:Address>
                                <ows:ElectronicMailAddress>test@test.nl</ows:ElectronicMailAddress>
                            </ows:Address>
                        </ows:ContactInfo>
                    </ows:ServiceContact>
                </ows:ServiceProvider>
            </root>
        '));

        $this->assertSame('test@test.nl', $parser->findContactEmail());
    }

    public function testReturnsEmptyArrayWhenNoKeywordsAreFound(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:Keywords />
            </root>
        '));

        $this->assertTrue(0 == \count($parser->findKeywords()));
    }

    public function testReturnsTheFoundKeywords(): void
    {
        $parser = new WFSResponseXMLParser(new \SimpleXMLElement('
            <root xmlns:ows="http://www.opengis.net/ows/1.1">
                <ows:ServiceIdentification>
                    <ows:Keywords>
                        <ows:Keyword>test1</ows:Keyword>
                        <ows:Keyword>test2</ows:Keyword>
                    </ows:Keywords>      
                </ows:ServiceIdentification>
            </root>
        '));

        $this->assertTrue(2 == \count($parser->findKeywords()));
        $this->assertSame(
            ['test1', 'test2'],
            $parser->findKeywords()
        );
    }
}
