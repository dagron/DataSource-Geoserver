<?php

namespace NijmegenSync\DataSource\Geoserver\Harvesting\WMS;

use NijmegenSync\DataSource\Geoserver\Harvesting\XMLParser;

class WMSResponseXMLParser extends XMLParser
{
    /**
     * GeoserverXMLParser constructor.
     *
     * @param \SimpleXMLElement $geoserver_response The XML object created from the response
     */
    public function __construct(\SimpleXMLElement $geoserver_response)
    {
        $this->xml = $geoserver_response;

        $this->registerNamespaces();
    }

    public function findLayers(): array
    {
        return $this->xml->xpath('//Layer');
    }

    /**
     * Registers the namespaces of the Nijmegen geoserver for proper parsing of the XML body.
     */
    private function registerNamespaces(): void
    {
        $this->xml->registerXPathNamespace('xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->xml->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $this->xml->registerXPathNamespace('ows', 'http://www.opengis.net/ows/1.1');
        $this->xml->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $this->xml->registerXPathNamespace('fes', 'http://www.opengis.net/fes/2.0');
        $this->xml->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');
        $this->xml->registerXPathNamespace('xs', 'http://www.w3.org/2001/XMLSchema');
        $this->xml->registerXPathNamespace('xml', 'http://www.w3.org/XML/1998/namespace');
    }
}
