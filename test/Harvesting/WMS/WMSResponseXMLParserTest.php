<?php

namespace NijmegenSync\Test\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\WMS\WMSResponseXMLParser;
use PHPUnit\Framework\TestCase;

class WMSResponseXMLParserTest extends TestCase
{
    public function testReturnsEmptyArrayWhenNoQueryableLayersAreFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <Layer />
            </root>
        '));

        $this->assertTrue(0 == \count($parser->getAllLayers()));
    }

    public function testReturnsLayerParsersForEachQueryableLayer(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <Layer />
                <Layer queryable="1">
                    <someNode />
                </Layer>
                <Layer queryable="1">
                    <someOtherNode />
                </Layer>
                <Layer />
            </root>
        '));

        $this->assertTrue(2 == \count($parser->getAllLayers()));
    }

    public function testReturnsEmptyStringWhenNoContactPersonIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <someNode />
                    </ContactPersonPrimary>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('', $parser->findContactPerson());
    }

    public function testReturnsContactPersonWhenFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <ContactPerson>test</ContactPerson>
                    </ContactPersonPrimary>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('test', $parser->findContactPerson());
    }

    public function testReturnsEmptyStringWhenNoContactOrganizationIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <someNode />
                    </ContactPersonPrimary>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('', $parser->findContactOrganization());
    }

    public function testReturnsContactOrganizationWhenFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <ContactOrganization>test</ContactOrganization>
                    </ContactPersonPrimary>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('test', $parser->findContactOrganization());
    }

    public function testReturnsEmptyStringWhenNoContactEmailIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <someNode />
                    </ContactPersonPrimary>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('', $parser->findContactEmail());
    }

    public function testReturnsContactEmailWhenFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactElectronicMailAddress>test@test.nl</ContactElectronicMailAddress>
                </ContactInformation>
            </root>
        '));

        $this->assertSame('test@test.nl', $parser->findContactEmail());
    }

    public function testReturnsEmptyStringWhenNoAccessConstraintIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <someNode />
                    </ContactPersonPrimary>
                </ContactInformation>
                <Service />
            </root>
        '));

        $this->assertSame('', $parser->findAccessRights());
    }

    public function testReturnsAccessConstraintWhenFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <Service>
                    <AccessConstraints>NONE</AccessConstraints>
                </Service>
            </root>
        '));

        $this->assertSame('NONE', $parser->findAccessRights());
    }

    public function testReturnsEmptyStringWhenNoOutputFormatIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <ContactInformation>
                    <ContactPersonPrimary>
                        <someNode />
                    </ContactPersonPrimary>
                </ContactInformation>
                <Service />
                <Capability />
            </root>
        '));

        $this->assertSame('', $parser->findDesiredOutputFormat());
    }

    public function testReturnsPNGWhenFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <Capability>
                    <Request>
                        <GetMap>
                            <Format>image/png</Format>
                        </GetMap>
                    </Request>
                </Capability>
            </root>
        '));

        $this->assertSame('image/png', $parser->findDesiredOutputFormat());
    }

    public function testReturnsJPGWhenNoPNGIsFound(): void
    {
        $parser = new WMSResponseXMLParser(new \SimpleXMLElement('
            <root>
                <Capability>
                    <Request>
                        <GetMap>
                            <Format>image/jpeg</Format>
                        </GetMap>
                    </Request>
                </Capability>
            </root>
        '));

        $this->assertSame('image/jpeg', $parser->findDesiredOutputFormat());
    }
}
